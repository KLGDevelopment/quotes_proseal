<?php

namespace App\Livewire;

use App\Models\Quote;
use App\Models\Customer;
use App\Livewire\Base\CrudComponent;
use App\Models\BranchOffice;
use App\Models\Division;
use App\Models\Equipment;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;

#[Layout('layouts.livewire-adminlte')]
class Quotes extends CrudComponent
{
    public array $customers = [];
    public array $fields = [];
    public bool $showSyncButton = false;

    public $masterLink;
    public $master_link_label;
    public $master_link_icon;
    
    public function mount($parentId = null)
    {
      
        $this->master_link_label = "Ver Cotización";
        $this->masterLink = "/quotes/%%rowId%%/details";
        $this->master_link_icon = "fa fa-eye";


    }

    public function create()
    {
        $this->resetForm();
        $this->fields = [
            'customer_id' => '',
            'status' => 0,
            'equipment_id' => '',
            'branch_office_id' => '',
            'division_id' => '',
            'money_type' => '',
        ];
        $this->showForm = true;
    }

    protected function model(): string
    {
        return Quote::class;
    }

    protected function view(): string
    {
        return 'livewire.generic-crud'; // ← usa la vista genérica
    }

    public function title(): string
    {
        return 'Cotizaciones';
    }

    protected function fields(): array
    {
        $statusOptions = Quote::$statusOptions;

        return [
            [
                'label' => 'ID',
                'name'  => 'id',
                'type'  => 'text',
                'show_in_form' => false,
                'show_in_index' => false,
                'sortable' => true,
            ],
            [
                'label' => 'N° OT',
                'name'  => 'work_order_number',
                'type'  => 'text',
                'show_in_form' => true,
                'sortable' => true,
            ],
            [
                'label' => 'Cliente',
                'name'  => 'customer_id',
                'type'  => 'select',
                'sortable' => true,
                'options' => Customer::orderBy('name')->get()->mapWithKeys(fn($c) => [$c->id => "{$c->vat} - {$c->name}"])->toArray()
            ],
            [
                'label' => 'Equipo',
                'name'  => 'equipment_id',
                'type'  => 'select',
                'show_in_form' => true,
                'sortable' => true,
                'options' => Equipment::get()->mapWithKeys(fn($c) => [$c->id => "{$c->brand} - {$c->model} - {$c->size}"])->toArray()
            ],
            [
                'label' => 'Moneda',
                'name'  => 'money_type',
                'type'  => 'select',
                'show_in_form' => true,
                'show_in_index' => false,
                'sortable' => true,
                'options' => Quote::$moneyArray,
                'default' => 0,
            ],
            [
                'label' => 'DA: Sucursal',
                'name'  => 'branch_office_id',
                'type'  => 'select',
                'show_in_form' => true,
                'sortable' => true,
                'options' => BranchOffice::orderBy('name')->get()->mapWithKeys(fn($c) => [$c->id => "{$c->name}"])->toArray()
            ],
            [
                'label' => 'DA: División',
                'name'  => 'division_id',
                'type'  => 'select',
                'show_in_form' => true,
                'options' => Division::orderBy('name')->get()->mapWithKeys(fn($c) => [$c->id => "{$c->name}"])->toArray()
            ],
            [
                'label' => 'Total Neto',
                'name'  => 'neto',
                'type'  => 'input',
                'show_in_form' => false,
                'sortable' => true,
                'format' => 'currency', 
                'class' => 'text-right'
            ],
            [
                'label' => 'Estado',
                'name' => 'status',
                'type' => 'select',
                'options' => $statusOptions,
                'sortable' => true,
                'default' => 0,
                'show_in_create' => false,
            ]
        ];
    }

    protected function rules(): array
    {
        return [
            'fields.customer_id' => 'required|exists:customers,id',
            'fields.status' => 'required|in:0,1,2,3',
        ];
    }

    protected function searchableFields(): array
    {
        return ['id', 'customer_id','status'];
    }

    public function searchableFieldTransformations(): array
    {
        return [
            'customer_id' => fn($value) => Customer::query()
                ->where('vat', 'like', "%{$value}%")
                ->orWhere('name', 'like', "%{$value}%")
                ->pluck('id')
                ->toArray(),
            'id' => fn($value) => is_numeric($value) ? [(int) $value] : [],
            'status' => function ($value) {
                $map = [
                    'digitando' => 1,
                    'creada' => 2,
                    'aprobada' => 3,
                    'rechazada' => 4,
                ];

                $value = strtolower($value);

                foreach ($map as $key => $code) {
                    if (str_contains($key, $value)) {
                        return [$code];
                    }
                }

                return []; // No match
            },
        ];
    }

    protected function customizeSortField(string $field)
    {
        // Asegura que el componente base haga join con la tabla customers
        return match ($field) {
            'customer_id' => 'customers.vat', // usa VAT para ordenamiento
            'status' => DB::raw("
                FIELD(status, 0, 1, 2, 3)
            "), // Esto ordena por el orden definido en el array
            default => $field,
        };
    }

    protected function sortJoins(): array
    {
        return [
            'customers' => ['id', 'customer_id'] // customers.id = quotes.customer_id
        ];
    }

    
    protected function displayTransformations(): array
    {
        return [
            'neto' => fn($value) => '$ ' . number_format((float)$value, 0, ',', '.'),
            'status' => fn($value) => match((int) $value) {
                0 => 'DIGITANDO',
                1 => 'CREADA',
                2 => 'APROBADA',
                3 => 'RECHAZADA',
                default => '',
            },
            'money_type' => fn($value) => Quote::$moneyArray[(int) $value] ?? '',
        ];
    }
}
