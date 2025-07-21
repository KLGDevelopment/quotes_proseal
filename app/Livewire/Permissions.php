<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Permission;
use App\Models\Role;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

#[Layout('layouts.livewire-adminlte')]
class Permissions extends Component
{
    use WithPagination;

    public $permissions, $name, $permissionId;
    public $search = '';
    public $isEdit = false;
    protected $listeners = ['deletePermission' => 'delete'];
    public $showForm = false;
    public $displayName;
    public $description;

    protected $paginationTheme = 'bootstrap';

    public function render()
    {
        $query = Permission::query();

        if ($this->search !== '') {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('display_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $this->permissions = $query->orderBy('id', 'desc')->get();

        return view('livewire.permissions');
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
        $this->reset(['name', 'permissionId', 'isEdit']);
        $this->showForm = true;
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|unique:permissions,name,' . $this->permissionId,
        ]);

        if ($this->permissionId) {
            $permission = Permission::find($this->permissionId);
            $permission?->update(['name' => $this->name, 'display_name' => $this->displayName, 'description' => $this->description]);
        } else {
            $permission = Permission::create(['name' => $this->name, 'display_name' => $this->displayName, 'description' => $this->description]);
        }


        $this->resetForm();
    }

    public function edit($id)
    {
        $permission = permission::findOrFail($id);
        $this->permissionId = $permission->id;
        $this->name = $permission->name;
        $this->displayName = $permission->display_name;
        $this->description = $permission->description;
        $this->isEdit = true;
        $this->showForm = true;
    }

    public function delete($id)
    {
        permission::destroy($id);
    }

    public function resetForm()
    {
        $this->reset(['name', 'permissionId', 'isEdit']);
        $this->showForm = false;
    }
}
