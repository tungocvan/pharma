<?php

namespace Modules\Admin\Livewire\Menus;

use Livewire\Component;
use Modules\Website\Models\Category;
use Spatie\Permission\Models\Permission;

class MenuForm extends Component
{
    public $menuId;
    public $isEdit = false;

    public $name, $url, $icon, $can;
    public $is_active = true;
    public $is_section = false;

    public function mount($id = null)
    {
        if ($id) {
            $this->isEdit = true;
            $this->menuId = $id;
            $menu = Category::findOrFail($id);

            $this->name = $menu->name;
            $this->url = $menu->url;
            $this->icon = $menu->icon;
            $this->can = $menu->can;
            $this->is_active = (bool)$menu->is_active;
            
            // Logic nhận diện section
            if (empty($menu->url) && $menu->children->count() > 0 && empty($menu->parent_id)) {
                 // Đây là logic tương đối, bạn có thể tick thủ công
            }
             // Nếu user đã chủ động set url null khi tạo
            $this->is_section = empty($menu->url);
        }
    }

    public function updatedIsSection($val) {
        if($val) $this->url = null;
    }

    public function save()
    {
        $this->validate(['name' => 'required']);

        $data = [
            'name' => $this->name,
            'url' => $this->is_section ? null : $this->url,
            'icon' => $this->icon,
            'can' => $this->can,
            'type' => 'menu',
            'is_active' => $this->is_active,
        ];
        
        // Mặc định tạo mới thì nằm cuối cùng (sort_order cao nhất)
        if (!$this->isEdit) {
            $data['sort_order'] = Category::menu()->max('sort_order') + 1;
        }

        Category::updateOrCreate(['id' => $this->menuId], $data);
        
        session()->flash('success', 'Đã lưu thông tin menu.');
        return redirect()->route('admin.menus.index');
    }

    public function render()
    {
        return view('Admin::livewire.menus.menu-form', [
            'permissions' => Permission::orderBy('name')->get()
        ]);
    }
}