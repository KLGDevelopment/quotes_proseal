<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Role;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

#[Layout('layouts.livewire-adminlte')]
class Roles extends Component
{
    use WithPagination;

    public $roles, $name, $roleId;
    public $search = '';
    public $isEdit = false;
    protected $listeners = ['deleteRole' => 'delete'];
    public $showForm = false;

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
        $this->reset(['name', 'roleId', 'isEdit']);
        $this->showForm = true;
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|unique:roles,name,' . $this->roleId,
        ]);

        if ($this->roleId) {
            Role::find($this->roleId)?->update([
                'name' => $this->name,
            ]);
        } else {
            Role::create([
                'name' => $this->name,
            ]);
        }

        $this->resetForm();
    }

    public function edit($id)
    {
        $role = Role::findOrFail($id);
        $this->roleId = $role->id;
        $this->name = $role->name;
        $this->isEdit = true;
        $this->showForm = true;
    }

    public function delete($id)
    {
        Role::destroy($id);
    }

    public function resetForm()
    {
        $this->reset(['name', 'roleId', 'isEdit']);
        $this->showForm = false;
    }
}
