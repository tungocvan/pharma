<?php

namespace Modules\Users\Livewire;

use Livewire\Component;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserForm extends Component
{
    public $name, $email, $password, $role;
    public $userId;
    public $isEdit = false;
    public $roles = [];
    public $message;

    protected $listeners = [
        'openModalUserCreate' => 'create',
        'openModalUserEdit' => 'edit'
    ];

    public function mount()
    {
        $this->roles = Role::all();
    }

    public function create()
    {
        $this->reset(['name', 'email', 'password', 'role', 'userId', 'isEdit']);
        dd('create');
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $this->isEdit = true;
        $this->userId = $id;

        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->getRoleNames()->first();

        $this->dispatch('openModalUserEdit');
    }

    public function store()
    {
        $this->validate([
            'name' => 'required|min:3',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role' => 'required'
        ]);

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
        ]);

        $user->assignRole($this->role);

        $this->dispatch('closeUserModal');
        $this->dispatch('refreshUserList');
    }

    public function update()
    {
        $user = User::findOrFail($this->userId);

        $this->validate([
            'name'  => 'required|min:3',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role'  => 'required'
        ]);

        $user->update([
            'name'  => $this->name,
            'email' => $this->email,
        ]);

        $user->syncRoles([$this->role]);

        $this->dispatch('closeUserModal');
        $this->dispatch('refreshUserList');
    }

    public function render()
    {
        return view('Users::livewire.user-form');
    }
}
