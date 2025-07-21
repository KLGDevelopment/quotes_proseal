<?php

namespace App\Livewire;

use App\Models\Permission;
use Livewire\Component;
use App\Models\User;
use App\Models\Role;

use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Hash;

#[Layout('layouts.livewire-adminlte')]
class Users extends Component
{
    use WithPagination;

    public $users, $name, $email, $password, $userId;
    public $permissionsList = [];
    public $rolesList = [];
    public $permissionsSelected = [];
    public $rolesSelected = [];
    public $search = '';
    public $isEdit = false;
    protected $listeners = ['deleteUser' => 'delete'];
    public $showForm = false;

    protected $paginationTheme = 'bootstrap';

    public function render()
    {
        $query = User::query();

        if ($this->search !== '') {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $this->users = $query->orderBy('id', 'desc')->get();
        $this->permissionsList = Permission::orderBy('name')->get();
        $this->rolesList = Role::orderBy('name')->get();

        return view('livewire.users');
    }

    public function clearSearch()
    {
        $this->search = '';
        $this->resetPage();
        $this->refreshData();
    }

    public function applySearch()
    {
        $this->resetPage();
        $this->refreshData();
    }

    public function refreshData()
    {
        $this->dispatch('$refresh');
    }

    public function create()
    {
        $this->reset(['name', 'email', 'password', 'userId', 'isEdit', 'permissionsSelected', 'rolesSelected']);
        $this->showForm = true;
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|min:2',
            'email' => 'required|email|unique:users,email,' . $this->userId,
            'password' => $this->userId ? 'nullable' : 'required|min:6',
        ]);

        if ($this->userId) {
            $user = User::find($this->userId);
            if ($user) {
                $user->name = $this->name;
                $user->email = $this->email;
                if ($this->password) {
                    $user->password = Hash::make($this->password);
                }
                $user->save();
            }
        } else {
            $user = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => Hash::make($this->password),
            ]);
        }

        // Use Laratrust helpers to sync permissions (teams) and roles
        $user?->syncPermissions($this->permissionsSelected);
        $user?->syncRoles($this->rolesSelected);

        $this->resetForm();
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $this->userId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->permissionsSelected = $user->permissions()->pluck('id')->toArray();
        $this->rolesSelected = $user->roles()->pluck('id')->toArray();
        $this->password = '';
        $this->isEdit = true;
        $this->showForm = true;
    }

    public function delete($id)
    {
        User::destroy($id);
    }

    public function resetForm()
    {
        $this->reset(['name', 'email', 'password', 'userId', 'isEdit', 'permissionsSelected', 'rolesSelected']);
        $this->showForm = false;
    }
}
