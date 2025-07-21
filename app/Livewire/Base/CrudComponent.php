<?php

namespace App\Livewire\Base;

use Livewire\Component;
use Livewire\WithPagination;

abstract class CrudComponent extends Component
{
    use WithPagination;
    
    public bool $isSyncing = false;
    public bool $isEdit = false;
    public bool $showForm = false;
    public string $search = '';
    public string $sortField = 'id';
    public string $sortDirection = 'desc';
    
    protected $paginationTheme = 'bootstrap';
    protected $listeners = ['deleteItem' => 'delete'];
    
    public $itemId;
    public array $fields = [];
    
    abstract protected function model(): string;
    abstract protected function rules(): array;
    abstract protected function view(): string;
    abstract protected function fields(): array;
    abstract protected function searchableFields(): array;
    abstract public function title(): string;
    
    public function getTitleProperty(): string
    {
        return method_exists($this, 'title') ? $this->title() : 'Gestión CRUD';
    }
    
    protected function displayTransformations(): array
    {
        return []; // Cada componente hijo puede sobreescribir esto
    }
    
    public function transformValue(string $field, mixed $value): mixed
    {
        $map = $this->displayTransformations();
        
        return $map[$field][$value] ?? (is_bool($value) ? ($value ? 'Sí' : 'No') : $value);
    }
    
    public function mount()
    {
        $this->sortField = session($this->sortSessionKey('field'), 'id');
        $this->sortDirection = session($this->sortSessionKey('direction'), 'desc');
    }
    
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
        
        session([
            $this->sortSessionKey('field') => $this->sortField,
            $this->sortSessionKey('direction') => $this->sortDirection,
        ]);
        
        $this->resetPage();
    }
    
    protected function sortSessionKey(string $type): string
    {
        return class_basename($this->model()) . "_sort_$type";
    }
    
    public function getItemsProperty()
    {
        $query = $this->model()::query();
        
        if (!empty($this->search)) {
            $search = strtolower(trim($this->search));
            
            $query->where(function ($q) use ($search) {
                foreach ($this->searchableFields() as $field) {
                    $transforms = $this->searchableFieldTransformations();
                    
                    if (array_key_exists($field, $transforms)) {
                        $mappedValue = $transforms[$field][$search] ?? null;
                        if ($mappedValue) {
                            $q->orWhere($field, $mappedValue);
                        }
                    }
                    
                    $q->orWhere($field, 'like', "%{$search}%");
                }
            });
        }
        
        return $query->orderBy($this->sortField, $this->sortDirection)->paginate(10);
    }
    
    public function create()
    {
        $this->reset(['fields', 'itemId', 'isEdit']);
        $this->showForm = true;
    }
    
    public function edit($id)
    {
        $model = $this->model()::findOrFail($id);
        $this->itemId = $model->id;
        $this->fields = $model->only(collect($this->fields())->pluck('name')->toArray());
        $this->isEdit = true;
        $this->showForm = true;
    }
    
    public function save()
    {
        $this->validate($this->rules());
        
        $modelClass = $this->model();
        
        if ($this->itemId) {
            $modelClass::findOrFail($this->itemId)->update($this->fields);
        } else {
            $modelClass::create($this->fields);
        }
        
        $this->resetForm();
    }
    
    public function delete($id)
    {
        $this->model()::destroy($id);
    }
    
    public function resetForm()
    {
        $this->reset(['fields', 'itemId', 'isEdit', 'showForm']);
    }
    
    public function clearSearch()
    {
        $this->search = '';
        $this->resetPage();
    }
    
    
    public function render()
    {
        return view($this->view(), [
            'items' => $this->items,
        ]);
    }
    
    public function paginationView()
    {
        return 'components.pagination.spanish';
    }
    
    public function syncFromOdoo(): void
    {
        $this->isSyncing = true;
        
        try {
            $controller = app(\App\Http\Controllers\OdooSyncController::class);
            $method = $this->odooSyncMethod();
            
            if (!method_exists($controller, $method)) {
                throw new \RuntimeException("Método $method no existe en OdooSyncController.");
            }
            
            $response = $controller->{$method}();
            $data = $response->getData(true);
            
            if (isset($data['status']) && $data['status'] === 'ok') {
                $this->dispatch('notify', type: 'success', message: $data['message'] ?? 'Sincronización exitosa.');
                $this->refreshData();
            } else {
                $this->dispatch('notify', type: 'error', message: $data['message'] ?? 'Error desconocido.');
            }
            
        } catch (\Throwable $e) {
            $this->dispatch('notify', type: 'error', message: 'Error: ' . $e->getMessage());
        }
        
        $this->isSyncing = false;
    }
    
    
}
