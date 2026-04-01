<?php

namespace Modules\Admin\Livewire\Menus;

use Livewire\Component;
use Livewire\WithFileUploads;
use Modules\Website\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;


class MenuTable extends Component
{
    use WithFileUploads;

    // --- IMPORT / EXPORT VARS ---
    public $showImportModal = false;
    public $importFile;
    // Xóa Menu
    public function delete($id)
    {
        Category::find($id)->delete();
        $this->dispatch('notify', content: 'Đã xóa menu.', type: 'success');
    }

    // Toggle Ẩn/Hiện
    public function toggleStatus($id)
    {
        $menu = Category::find($id);
        if ($menu) {
            $menu->update(['is_active' => !$menu->is_active]);
            $this->dispatch('notify', content: 'Đã cập nhật trạng thái.', type: 'success');
        }
    }

    // --- LOGIC KÉO THẢ QUAN TRỌNG ---
    public function updateMenuOrder($list)
    {
        // $list là mảng phân cấp được gửi từ JS
        // Structure: [{id: 1, children: [{id: 2}, {id: 3}]}, {id: 4}]

        $this->updateRecursive($list, null);

        $this->dispatch('notify', content: 'Đã lưu cấu trúc menu mới.', type: 'success');
    }

    private function updateRecursive($items, $parentId)
    {
        foreach ($items as $index => $item) {
            // Cập nhật cha và thứ tự
            Category::where('id', $item['id'])->update([
                'parent_id' => $parentId,
                'sort_order' => $index
            ]);

            // Nếu có con, đệ quy tiếp
            if (isset($item['children']) && !empty($item['children'])) {
                $this->updateRecursive($item['children'], $item['id']);
            }
        }
    }

    // --- 1. CHỨC NĂNG DUPLICATE (NHÂN BẢN) ---
    public function duplicate($id)
    {
        $original = Category::find($id);
        if (!$original) return;

        DB::transaction(function () use ($original) {
            $this->recursiveDuplicate($original, $original->parent_id);
        });

        $this->dispatch('notify', content: 'Đã nhân bản menu thành công.', type: 'success');
    }

    private function recursiveDuplicate($original, $parentId)
    {
        // 1. Sao chép bản ghi
        $new = $original->replicate();
        $new->name = $original->name . ' (Copy)'; // Đổi tên để nhận biết
        $new->parent_id = $parentId;
        $new->slug = null; // Reset slug để tránh lỗi unique (nếu có)
        $new->sort_order = $original->sort_order + 1; // Đẩy xuống dưới 1 chút
        $new->save();

        // 2. Tìm các con của bản ghi gốc và nhân bản tiếp
        $children = Category::where('parent_id', $original->id)->get();
        foreach ($children as $child) {
            $this->recursiveDuplicate($child, $new->id); // Gán vào cha mới
        }
    }

    // --- 2. CHỨC NĂNG EXPORT ---
    public function export()
    {
        // Lấy cấu trúc cây để export
        $menus = Category::menu()
            ->whereNull('parent_id')
            ->with('children')
            ->orderBy('sort_order')
            ->get();

        // Build data đệ quy
        $data = $this->buildTreeData($menus);

        // Encode JSON một lần
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        // ===== LƯU FILE SEEDER =====
        $seederPath = base_path('Modules/Website/database/Seeders/menu.json');

        // Đảm bảo thư mục tồn tại
        File::ensureDirectoryExists(dirname($seederPath));

        // Ghi file
        File::put($seederPath, $json);

        // ===== DOWNLOAD =====
        $fileName = 'menu_backup_' . date('Y-m-d_His') . '.json';

        return response()->streamDownload(function () use ($json) {
            echo $json;
        }, $fileName);
    }


    private function buildTreeData($categories)
    {
        $result = [];
        foreach ($categories as $cat) {
            $item = [
                'name' => $cat->name,
                'url' => $cat->url,
                'icon' => $cat->icon,
                'can' => $cat->can,
                'is_active' => $cat->is_active,
                'children' => []
            ];

            if ($cat->children->isNotEmpty()) {
                $item['children'] = $this->buildTreeData($cat->children);
            }

            $result[] = $item;
        }
        return $result;
    }

    // --- 3. CHỨC NĂNG IMPORT ---


    public function import()
    {
        try {
            // ===== 1. XÁC ĐỊNH NGUỒN FILE =====
            if ($this->importFile) {
                // Có upload file
                $this->validate([
                    'importFile' => 'mimes:json,txt|max:2048'
                ]);

                $content = file_get_contents($this->importFile->getRealPath());
            } else {
                // Không upload → dùng file seed mặc định
                $seedPath = base_path('Modules/Website/database/Seeders/menu.json');

                if (!File::exists($seedPath)) {
                    throw new \Exception('Không có file upload và không tìm thấy menu.json trong Seeder.');
                }

                $content = File::get($seedPath);
            }

            // ===== 2. PARSE JSON =====
            $json = json_decode($content, true);

            if (!is_array($json)) {
                throw new \Exception('File JSON không hợp lệ.');
            }

            // ===== 3. BIẾN ĐẾM =====
            $countSuccess = 0;
            $countSkip = 0;

            DB::transaction(function () use ($json, &$countSuccess, &$countSkip) {

                // sort_order cho root menu
                $maxSort = Category::menu()
                    ->whereNull('parent_id')
                    ->max('sort_order') ?? 0;

                foreach ($json as $item) {
                    $maxSort++;
                    $this->recursiveImport(
                        $item,
                        null,
                        $maxSort,
                        $countSuccess,
                        $countSkip
                    );
                }
            });

            // ===== 4. RESET UI =====
            $this->showImportModal = false;
            $this->importFile = null;

            // ===== 5. NOTIFY =====
            $msg = "Import hoàn tất: Thêm mới {$countSuccess}, Bỏ qua {$countSkip} (do trùng).";
            $type = $countSuccess > 0 ? 'success' : 'warning';

            $this->dispatch('notify', content: $msg, type: $type);

        } catch (\Exception $e) {
            $this->addError('importFile', 'Lỗi: ' . $e->getMessage());
        }
    }


    private function recursiveImport($item, $parentId, $sortOrder, &$countSuccess, &$countSkip)
    {
        // 1. Xác định tiêu chí trùng lặp
        // Một menu được coi là trùng nếu: Cùng Loại, Cùng Cha, Cùng Tên và Cùng URL
        $criteria = [
            'type' => 'menu',
            'parent_id' => $parentId,
            'name' => $item['name'],
            'url' => $item['url'] ?? null,
        ];

        // 2. Kiểm tra tồn tại
        $existingMenu = Category::where($criteria)->first();

        if ($existingMenu) {
            // [TRÙNG] -> Bỏ qua tạo mới
            $countSkip++;
            $currentId = $existingMenu->id; // Lấy ID cũ để dùng cho con
        } else {
            // [KHÔNG TRÙNG] -> Tạo mới
            $newMenu = Category::create(array_merge($criteria, [
                'icon' => $item['icon'] ?? null,
                'can' => $item['can'] ?? null,
                'is_active' => $item['is_active'] ?? true,
                'sort_order' => $sortOrder,
                // Các trường meta khác nếu có
            ]));

            $countSuccess++;
            $currentId = $newMenu->id;
        }

        // 3. Xử lý Đệ quy cho Menu con (Children)
        // Lưu ý: Vẫn chạy đệ quy ngay cả khi menu cha bị trùng (Skip),
        // vì có thể trong menu cha cũ chưa có các menu con mới này.
        if (!empty($item['children'])) {
            $childSort = 0;
            foreach ($item['children'] as $child) {
                // Truyền $currentId (ID của cha vừa tìm thấy hoặc vừa tạo) xuống
                $this->recursiveImport($child, $currentId, $childSort++, $countSuccess, $countSkip);
            }
        }
    }

    public function render()
    {
        // Lấy toàn bộ menu, sắp xếp theo thứ tự
        // Chúng ta lấy dạng phẳng (Flat), việc phân cấp sẽ do View xử lý
        $menus = Category::menu()
            ->with('children') // Eager load để tối ưu
            ->whereNull('parent_id') // Lấy gốc trước
            ->orderBy('sort_order')
            ->get();

        return view('Admin::livewire.menus.menu-table', ['menus' => $menus]);
    }
}
