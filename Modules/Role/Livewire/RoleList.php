<?php

namespace Modules\Role\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\File;

class RoleList extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $formVisible = false;
    public $isEditMode = false;
    public $isModuleMode = false;
    public $name;
    public $roleId;
    public $modules = [];
    public $module = '';
    public $permissionsByModule = [];
    public $selectedPermissions = [];
    public $selectAll = false;

    protected $rules = [
        'name' => 'required|string|max:255',
    ]; 

    public function mount()
    {
        $this->loadPermissions();
    }

    public function loadPermissions()
    {
        $permissions = Permission::all();

        $this->permissionsByModule = $permissions->groupBy(function ($perm) {
            return explode('-', $perm->name)[0] ?? 'Khác';
        })->map(fn($group) => $group->toArray())->toArray();
    }

    public function updatedSelectAll($value)
    {
        $this->selectedPermissions = $value
            ? Permission::pluck('name')->toArray()
            : [];
    }

    public function create()
    {
        $this->reset(['name', 'roleId', 'selectedPermissions']);
        $this->isEditMode = false;
        $this->formVisible = true;
        $this->resetPage();
    }

    public function edit($id)
    {
        $role = Role::findOrFail($id);
        $this->roleId = $role->id;
        $this->name = $role->name;
        $this->selectedPermissions = $role->permissions->pluck('name')->toArray();
        $this->isEditMode = true;
        $this->formVisible = true;
        $this->resetPage();
    }
   
    /**
 * Lấy danh sách module trong thư mục /Modules chưa có quyền
 */
public function permission()
{
    $pathModule = base_path('Modules/');
    $directories = File::directories($pathModule);
    $directoryNames = array_map('basename', $directories);

    $this->modules = [];

    foreach ($directoryNames as $moduleName) {
        $prefix = strtolower($moduleName);
        $exists = Permission::where('name', 'like', "{$prefix}-%")->exists();

        // Nếu chưa có quyền nào của module này thì thêm vào danh sách hiển thị
        if (!$exists) {
            $this->modules[] = $moduleName;
        }
    }

    $this->isModuleMode = true;
}

/**
 * Sinh ra các quyền mặc định: list, create, edit, delete cho module đã chọn
 */
public function storePermission()
{
    $this->validate([
        'module' => 'required|string',
    ]);

    $name = strtolower(trim($this->module));

    $permissionsArray = [
        'view_'.$name,
        'create_'.$name,
        'edit_'.$name,
        'delete_'.$name,
     ];

    foreach ($permissionsArray as $permissionName) {
        // ✅ Dùng firstOrCreate để tránh lỗi trùng unique
        Permission::firstOrCreate(['name' => $permissionName]);
    }

    // Cập nhật lại danh sách modules chưa có quyền
    $this->permission();

    // Ẩn form chọn module
    $this->isModuleMode = false;
    $this->module = '';

    // ✅ Livewire 3 — thông báo
    $this->dispatch('notify', [
        'message' => 'Đã tạo quyền mặc định cho module ' . strtoupper($name),
    ]);
}


    public function save()
    {
        $this->validate();

        $role = $this->isEditMode
            ? Role::findOrFail($this->roleId)
            : Role::create(['name' => $this->name]);

        $role->syncPermissions($this->selectedPermissions);

        $this->formVisible = false;
        $this->dispatch('notify', [
            'message' => $this->isEditMode ? 'Cập nhật thành công!' : 'Tạo mới thành công!'
        ]);
    }

    public function cancel()
    {
        $this->formVisible = false;
    }

    public function togglePermission($permissionName)
    {
        if (in_array($permissionName, $this->selectedPermissions)) {
            $this->selectedPermissions = array_diff($this->selectedPermissions, [$permissionName]);
        } else {
            $this->selectedPermissions[] = $permissionName;
        }
    }

    public function toggleModule($module)
    {
        $modulePermissions = Permission::where('name', 'like', "{$module}-%")->pluck('name')->toArray();
        $hasAll = collect($modulePermissions)->every(fn($p) => in_array($p, $this->selectedPermissions));

        if ($hasAll) {
            $this->selectedPermissions = array_diff($this->selectedPermissions, $modulePermissions);
        } else {
            $this->selectedPermissions = array_unique(array_merge($this->selectedPermissions, $modulePermissions));
        }
    }

    public function render()
    {
        $roles = Role::query()
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderByDesc('id')
            ->paginate($this->perPage);
            return view('Role::livewire.role-list',compact('roles'));
    }
}
