<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Profile;
use App\Models\Role;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

#[Layout('layouts.livewire-adminlte')]
class Profiles extends Component
{
    use WithPagination;

    public $profiles, $name, $profileId;
    public $rolesList = [];
    public $rolesSelected = [];
    public $search = '';
    public $isEdit = false;
    protected $listeners = ['deleteProfile' => 'delete'];
    public $showForm = false;

    protected $paginationTheme = 'bootstrap';

    public function render()
    {
        $query = Profile::query();

        if ($this->search !== '') {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        $this->profiles = $query->orderBy('id', 'desc')->get();
        $this->rolesList = Role::orderBy('name')->get();

        return view('livewire.profiles');
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
        $this->reset(['name', 'profileId', 'isEdit', 'rolesSelected']);
        $this->showForm = true;
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|unique:profiles,name,' . $this->profileId,
        ]);

        if ($this->profileId) {
            $profile = Profile::find($this->profileId);
            $profile?->update(['name' => $this->name]);
        } else {
            $profile = Profile::create(['name' => $this->name]);
        }

        // Use Laratrust helper to sync roles with the profile
        $profile?->syncRoles($this->rolesSelected);

        $this->resetForm();
    }

    public function edit($id)
    {
        $profile = Profile::findOrFail($id);
        $this->profileId = $profile->id;
        $this->name = $profile->name;
        $this->rolesSelected = $profile->roles()->pluck('id')->toArray();
        $this->isEdit = true;
        $this->showForm = true;
    }

    public function delete($id)
    {
        Profile::destroy($id);
    }

    public function resetForm()
    {
        $this->reset(['name', 'profileId', 'isEdit', 'rolesSelected']);
        $this->showForm = false;
    }
}
