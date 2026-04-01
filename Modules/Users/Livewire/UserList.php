<?php

namespace Modules\Users\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\User;
use Spatie\Permission\Models\Role;
// use App\Helpers\TnvUserHelper;
// use App\Exports\UsersExport;
// use Maatwebsite\Excel\Facades\Excel;
// use Barryvdh\DomPDF\Facade\Pdf;
// use Carbon\Carbon;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\View;
use Livewire\Attributes\Validate;

class UserList extends Component
{
    use WithPagination, WithFileUploads;

    // Table & filters
    public $perPage = 5;
    public $search = '';
    public $sortField = 'id';
    public $sortDirection = 'asc';

    // Selection 
    public $selectedUsers = [];
    public $selectAll = false;

    // Modal states
    public $isEdit = false;

    // User fields
    public $userId = null;
    public $name;
    #[Validate()]
    public $username;

    #[Validate()]
    public $email;
    #[Validate()]
    public $password;
    public $birthdate;
    public $google_id;
    public $referral_code;
    public $is_admin = 0;
    public ?string $message = null;

    // Role
    public $role = null; // for create/edit
    public $selectedRoleId = null; // for role modal

    public $profile = [
        'full_name' => '',
        'gender' => '',
        'birthdate' => '',
        'id_number' => '',
        'job' => '',
        'bio' => '',
        'mail_password' => ''
    ];

    public $shipping_info = [
        'address' => '',
        'email' => '',
        'company_name' => '',
        'tax_code' => '',
        'phone' => '',
        'website' => '',
    ];

    protected $listeners = [
        'refreshUsers' => '$refresh',
    ];

    protected $queryString = ['search', 'sortField', 'sortDirection', 'perPage'];

    protected $rules = [
        'name' => 'nullable|string',
        'email' => 'required|string|email|max:255|unique:users,email',
        'username' => 'nullable|string|unique:users,username',
        'password' => 'required|string|min:6',
    ];

    protected $messages = [
        'email.required'       => 'Email đăng nhập không được bỏ trống',
        'email.email'          => 'Email không đúng định dạng',
        'email.unique'         => 'Email này đã tồn tại',
        'password.required'    => 'Mật khẩu không được bỏ trống',
        'password.min'         => 'Mật khẩu phải có ít nhất 6 ký tự',
        'username.unique'         => 'Username này đã tồn tại',
    ];

    protected $rulesUpdate = [
        'name' => 'required|string|max:255',
        'username' => 'nullable|string|unique:users,username',
        'email' => 'required|string|email|max:255',
        'password' => 'nullable|string|min:6',
    ];


    // -------- Computed properties --------
    public function getUsersProperty()
    {

        $query = User::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%");
            });
        }

        $query->orderBy($this->sortField, $this->sortDirection);

        return $query
            ->paginate($this->perPage)
            ->appends(['search' => $this->search]);
    }

    #[On('refreshUsers')]
    public function refreshUsers($message = null)
    {
        // Nếu có message thì gán để hiển thị
        if ($message) {
            $this->message = $message;
        }
    }


    public function getRolesProperty()
    {
        return Role::orderBy('name')->pluck('name', 'id')->toArray();
    }

    // -------- Table & selection --------
    public function toggleSelectAll()
    {
        $this->selectedUsers = $this->selectAll ? $this->users->pluck('id')->toArray() : [];
    }

    public function updatedSelectedUsers()
    {
        $this->message !== null && ($this->message = null);
    }

    public function updateUserRole()
    {
        // Không validate cứng, vì role/referral có thể chọn 1 trong 2
        $users = User::whereIn('id', $this->selectedUsers)->get();

        if ($users->isEmpty()) {
            session()->flash('error', 'Không có user nào được chọn!');
            return;
        }

        $role = null;

        // ✅ Nếu có selectedRoleId → xử lý role
        if (!empty($this->selectedRoleId)) {
            $role = Role::find($this->selectedRoleId);

            if (!$role) {
                session()->flash('error', 'Role không tồn tại!');
                return;
            }
        }

        foreach ($users as $user) {
            // ✅ Cập nhật role nếu có selectedRoleId
            if ($role) {
                $user->syncRoles([$role->name]);
            }

            // ✅ Cập nhật referral_code nếu có nhập
            if (!empty($this->referral_code)) {
                $user->referral_code = $this->referral_code;
                $user->save();
            }
        }

        // Reset
        $this->closeModalRole();
        $this->selectedUsers = [];
        $this->selectedRoleId = null;
        $this->referral_code = null;

        session()->flash('message', 'Cập nhật thành công!');
        $this->dispatch('modalRole'); // đóng modal
    }

    public function updatedPerPage()
    {
        $this->resetPage();
        // Cập nhật giá trị perPage
        // $this->perPage = (int) $value;

        // // Reset page về 1 mà KHÔNG làm thay đổi route thành /livewire/update
        // $this->resetPage();

        // // Giữ nguyên query string đúng (user?page=1&perPage=10)
        // $this->dispatch('refreshUsers');
    }

    // -------- Modal control --------
    public function openModal()
    {
        $this->resetForm();
        $this->dispatch('show-modal-user');
    }

    public function closeModal()
    {

        $this->dispatch('close-modal-user');
        $this->resetForm();
    }

    public function openModalRole()
    {
        if (empty($this->selectedUsers)) {
            session()->flash('error', 'Vui lòng chọn ít nhất một người dùng.');
            return;
        }

        if (count($this->selectedUsers) === 1) {
            $u = User::with('roles')->find($this->selectedUsers[0]);
            $this->selectedRoleId = $u?->roles->pluck('id')->first() ?? null;
        } else {
            $this->selectedRoleId = null;
        }

        $this->dispatch('show-modal-role');
    }

    public function closeModalRole()
    {
        $this->dispatch('close-modal-role');
        $this->selectedRoleId = null;
    }

    #[On('reset-form')]
    public function resetForm()
    {
        $this->reset(['userId', 'name', 'email', 'password']);
    }

    // -------- CRUD operations --------
    public function createUser()
    {
        // chỉ validate các field cần thiết $this->rulesCreate
        //$validated = $this->validate($this->rulesCreate);

        // dữ liệu cần đưa vào table khi tạo mới
        $data = [
            'email' => $this->email,
            'password' => $this->password,
            'name' => $this->name ?? null,
            'username' => $this->username ?? null,
            'is_admin' => $this->is_admin ?? 0,
            'birthdate' => $this->birthdate ?? null,
            'referral_code' => $this->referral_code ?? null,
            'google_id' => $this->google_id ?? null,
            'role_name' => $this->role ?? 'User',
        ];
        dd($data);
        // $result = TnvUserHelper::register($data);

        // if ($result['status'] === 'success') {
        //     $id = $result['data']['id']; // lấy User model thực sự
        //     $user = User::find($id);
        //     if (!$user) {
        //         $user->setOption('profile', $this->profile);
        //         $user->setOption('shipping_info', $this->shipping_info);
        //     }


        //     $this->dispatch('refreshUsers', message: '✅ Tạo người dùng thành công!');
        // } else {
        //     session()->flash('error', '❌ ' . $result['message']);
        // }
    }

    public function editUser($id)
    {
        $user = User::find($id);
        if (!$user) {
            session()->flash('error', 'Không tìm thấy người dùng!');
            return;
        }
        dd($user);
        $this->shipping_info = $user->getOption('shipping_info', []);
        $this->profile = $user->getOption('profile', []);
        $this->userId = $user->id;
        $this->name = $user->name;
        $this->username = $user->username;
        $this->password = null;
        $this->email = $user->email;
        $this->birthdate = $user->birthdate;
        $this->google_id = $user->google_id;
        $this->referral_code = $user->referral_code;
        $this->isEdit = true;
        $this->role = $user->roles->pluck('id')->first() ?? null;

        $this->dispatch('show-modal-user');
    }

    public function updateUser()
    {

        $data = [
            'name' => $this->name ?? '',
            'email' => $this->email ?? '',
            'username' => $this->username ?? '',
            'birthdate' => $this->birthdate ?? null,
            'google_id' => $this->google_id ?? '',
            'referral_code' => $this->referral_code ?? '',
        ];

        if (!empty($this->password)) {
            $data['password'] = $this->password;
        }
        dd($data);
       
    }

    public function deleteUser($id)
    {
        $user = User::find($id);
        if (!$user) {
            session()->flash('error', 'Không tìm thấy người dùng.');
            return;
        }

        if ($user->is_admin == -1) {
            session()->flash('error', 'Không thể xóa tài khoản admin.');
            return;
        }

        $user->delete();
        $this->dispatch('refreshUsers', message: '🗑️ Xóa người dùng thành công!');
    }

    public function deleteSelectedUsers()
    {
        if (empty($this->selectedUsers)) {
            session()->flash('error', 'Chưa chọn người dùng nào!');
            return;
        }

        $users = User::whereIn('id', $this->selectedUsers)->get();
        foreach ($users as $user) {
            if ($user->is_admin != -1) {
                $user->delete();
            }
        }

        $this->selectedUsers = [];
        //session()->flash('message', '🗑️ Đã xóa người dùng được chọn!');
        $this->dispatch('refreshUsers', message: '🗑️ Đã xóa người dùng được chọn!');
    }

    // -------- Role assignment --------
    public function assignRoleToUsers()
    {
        $this->validate([
            'selectedRoleId' => 'required|exists:roles,id',
        ]);

        $role = Role::find($this->selectedRoleId);
        if (!$role) {
            session()->flash('error', 'Vai trò không tồn tại!');
            return;
        }

        $users = User::whereIn('id', $this->selectedUsers)->get();
        foreach ($users as $user) {
            $user->syncRoles([$role->name]);
        }

        $this->closeModalRole();
        $this->selectedUsers = [];
        session()->flash('message', '✅ Cập nhật vai trò thành công!');
        $this->dispatch('refreshUsers');
    }

    // -------- Approve / Verify --------
    public function approveUser($id)
    {
        $user = User::findOrFail($id);
        if (is_null($user->email_verified_at)) {
            $user->update(['email_verified_at' => now()]);
            $this->dispatch('refreshUsers', message: '✅ Người dùng đã được duyệt!');
        } else {
            $user->update(['email_verified_at' => null]);
            $this->dispatch('refreshUsers', message: '✅ Đã duyệt bỏ xác thực!');
        }
    }
  

    // -------- Sorting --------
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->setPage(1);
        $this->message !== null && ($this->message = null);
    }

    public function render()
    {
        return view('Users::livewire.user-list', [
            'users' => $this->users,
            'roles' => $this->roles,
        ]);     
    }
}
