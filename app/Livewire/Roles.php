<?php

namespace App\Livewire;

use App\Models\Permission;
use Livewire\Component;
use App\Models\Role;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

#[Layout('layouts.livewire-adminlte')]
class Roles extends Component
{
    use WithPagination;

    public $roles, $name, $roleId;
    public $permissionsList = [];
    public $permissionsSelected = [];
    public $search = '';
    public $isEdit = false;
    protected $listeners = ['deleteRole' => 'delete'];
    public $showForm = false;
    public $displayName;
    public $description;


    protected $paginationTheme = 'bootstrap';

    public function render()
    {
        $query = Role::query();

        if ($this->search !== '') {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        $this->roles = $query->orderBy('id', 'desc')->get();
        $this->permissionsList = Permission::orderBy('name')->get();

        return view('livewire.roles');
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
        $this->reset(['name', 'roleId', 'isEdit', 'permissionsSelected']);
        $this->showForm = true;
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|unique:roles,name,' . $this->roleId,
        ]);


        if ($this->roleId) {
            $role = Role::find($this->roleId);
            $role?->update(['name' => $this->name, 'display_name' => $this->displayName, 'description' => $this->description]);
        } else {
            $role = Role::create(['name' => $this->name, 'display_name' => $this->displayName, 'description' => $this->description]);
        }

        // Use Laratrust helper to sync roles with the permission
        $role?->syncPermissions($this->permissionsSelected);

        $this->resetForm();
    }

    public function edit($id)
    {
        $role = Role::findOrFail($id);
        $this->roleId = $role->id;
        $this->name = $role->name;
        $this->displayName = $role->display_name;
        $this->description = $role->description;
        $this->permissionsSelected = $role->permissions()->pluck('id')->toArray();
        $this->isEdit = true;
        $this->showForm = true;
    }

    public function delete($id)
    {
        Role::destroy($id);
    }

    public function resetForm()
    {
        $this->reset(['name', 'roleId', 'isEdit','permissionsSelected']);
        $this->showForm = false;
    }
}
