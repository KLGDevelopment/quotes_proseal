<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\Role;
use App\Models\Profile;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Hash;

#[Layout('layouts.livewire-adminlte')]
class Users extends Component
{
    use WithPagination;

    public $users, $name, $email, $password, $userId;
    public $profilesList = [];
    public $rolesList = [];
    public $profilesSelected = [];
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
        $this->profilesList = Profile::orderBy('name')->get();
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
        $this->reset(['name', 'email', 'password', 'userId', 'isEdit', 'profilesSelected', 'rolesSelected']);
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

        // Use Laratrust helpers to sync profiles (teams) and roles
        $user?->syncTeams($this->profilesSelected);
        $user?->syncRoles($this->rolesSelected);

        $this->resetForm();
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $this->userId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->profilesSelected = $user->profiles()->pluck('id')->toArray();
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
        $this->reset(['name', 'email', 'password', 'userId', 'isEdit', 'profilesSelected', 'rolesSelected']);
        $this->showForm = false;
    }
}
