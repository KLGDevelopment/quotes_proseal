<?php

namespace App\Livewire\Base;

use Livewire\Component;
use Livewire\WithPagination;

abstract class CrudComponent extends Component
{
    use WithPagination;

    public bool $showSyncButton = true;
    public bool $isSyncing = false;
    public bool $isEdit = false;
    public bool $showForm = false;
    public string $search = '';
    protected bool $afterSave = false;

    protected string $parentKey = 'quote_id';
    public int $parentId;

    public string $sortField = 'id';
    public string $sortDirection = 'desc';

    protected $paginationTheme = 'bootstrap';
    protected $listeners = ['deleteItem' => 'delete', 'approveBasic' => 'approveBasic', 'approveAdvanced' => 'approveAdvanced', 'sentToOdoo' => 'sentToOdoo'];

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
        return $this->title();
    }

    protected function isAssoc(array $array): bool
    {
        return array_keys($array) !== range(0, count($array) - 1);
    }
    

    public function transformValue(string $field, mixed $value): string
    {
        $displayTransformations = $this->displayTransformations();

        if (
            isset($displayTransformations[$field]) &&
            is_callable($displayTransformations[$field])
        ) {
            return $displayTransformations[$field]($value);
        }

        $definition = collect($this->fields())->firstWhere('name', $field);

        if (isset($definition['options']) && $this->isAssoc($definition['options'])) {
            return $definition['options'][$value] ?? (string) $value;
        }

        return is_bool($value) ? ($value ? 'Sí' : 'No') : (string) $value;
    }


    public function mount($parentId = null)
    {
        if ($parentId) {
            $this->parentId = $parentId;
        }

        // Defaults: if component declares a sortable field, default sort is that field ASC.
        // Otherwise, keep legacy defaults (id DESC).
        $defaultField = $this->isSortable() ? $this->getSortableField() : 'id';
        $defaultDirection = $this->isSortable() ? 'asc' : 'desc';

        $this->sortField = session($this->sortSessionKey('field'), $defaultField);
        $this->sortDirection = session($this->sortSessionKey('direction'), $defaultDirection);
    }

    public function sortBy($field)
    {
        $this->sortField = $field;
        $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';

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
        $query = method_exists($this, 'baseQuery') ? $this->baseQuery() : $this->model()::query();

        if (!empty($this->search)) {
            $search = strtolower(trim($this->search));

            $query->where(function ($q) use ($search) {
                foreach ($this->searchableFields() as $field) {
                    $transforms = method_exists($this, 'searchableFieldTransformations') ? $this->searchableFieldTransformations() : [];

                    if (array_key_exists($field, $transforms)) {
                        $mappedValue = ($transforms[$field])($search);
                        if (!empty($mappedValue)) {
                            $q->orWhereIn($field, (array) $mappedValue);
                        }
                    } else {
                        $q->orWhere($field, 'like', "%{$search}%");
                    }
                }
            });
        }

        if (in_array($this->sortField, array_column($this->fields(), 'name'))) {
            $query->orderBy($this->sortField, $this->sortDirection);
        } else {
            $query->orderBy('id', 'desc');
        }

        return $query->paginate(10);
    }

    public function create()
    {
        $this->reset(['fields', 'itemId', 'isEdit']);
        $this->fields = collect($this->fields())
            ->pluck('name')
            ->mapWithKeys(fn($name) => [$name => null])
            ->toArray();
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
       
        foreach ($this->fields() as $field) {
            $isHidden = ($field['show_in_form'] ?? true) === false;
            $isCalculated = ($field['calculated'] ?? false) === true;
            $readOnly = ($field['read_only'] ?? false) === true;
            


            if ($isHidden && !$isCalculated) {
                unset($this->fields[$field['name']]);
            }

            if (array_key_exists('default', $field)) {
               $this->fields[$field['name']] = $field['default'];
            }

   
            if ($readOnly) {
              
                unset($this->fields[$field['name']]);
            }


        }

        $modelClass = $this->model();

        if (property_exists($this, 'parentKey') && isset($this->parentId)) {
            $this->fields[$this->parentKey] = $this->parentId;
        }

        if ($this->itemId) {
            $modelClass::findOrFail($this->itemId)->update($this->fields);
        } else {
            $created = $modelClass::create($this->fields);
            $this->itemId = $created->getKey();
        }
       
   
        if (!$this->afterSave)
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

            $type = ($data['status'] ?? '') === 'ok' ? 'success' : 'error';
            $message = $data['message'] ?? 'Sincronización finalizada.';

            $this->dispatch('notify', type: $type, message: $message);

            if ($type === 'success') {
                $this->resetPage();
            }
        } catch (\Throwable $e) {
            $this->dispatch('notify', type: 'error', message: 'Error: ' . $e->getMessage());
        }

        $this->isSyncing = false;
    }

    public function isSortable(): bool
    {
        return property_exists($this, 'sortableField') && !is_null($this->sortableField);
    }

    public function getSortableField(): string
    {
        return $this->sortableField ?? 'order';
    }

    protected function displayTransformations(): array
    {
        return [];
    }
}
