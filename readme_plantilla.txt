// 📄 PLANTILLA BASE PARA COMPONENTES CRUD CON LIVEWIRE 3 + BLADE GENÉRICO

// 📁 COMPONENTE LIVEWIRE (ej: App/Livewire/Quotes.php)

namespace App\Livewire;

use App\Livewire\Base\CrudComponent;
use App\Models\Quote;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;

#[Layout('layouts.livewire-adminlte')]
class Quotes extends CrudComponent
{
    public function create()
    {
        $this->resetForm();
        $this->fields = [
            'customer_id' => '',
            'status' => 1,
        ];
        $this->showForm = true;
    }

    protected function model(): string { return Quote::class; }
    protected function view(): string { return 'livewire.generic-crud'; }
    public function title(): string { return 'Cotizaciones'; }

    protected function fields(): array
    {
        $statusOptions = [
            1 => 'DIGITANDO',
            2 => 'CREADA',
            3 => 'APROBADA',
            4 => 'RECHAZADA',
        ];

        return [
            [
                'label' => 'Cliente',
                'name'  => 'customer_id',
                'type'  => 'select',
                'sortable' => true,
                'options' => Customer::get()->mapWithKeys(fn($c) => [$c->id => "$c->vat - $c->name"])->toArray(),
            ],
            [
                'label' => 'Total',
                'name' => 'total',
                'type' => 'input',
                'format' => 'currency',
                'class' => 'text-right',
                'sortable' => true,
            ],
            [
                'label' => 'Estado',
                'name' => 'status',
                'type' => 'select',
                'options' => $statusOptions,
                'sortable' => true,
            ],
        ];
    }

    protected function rules(): array
    {
        return [
            'fields.customer_id' => 'required|exists:customers,id',
            'fields.status' => 'required|in:1,2,3,4',
        ];
    }

    protected function searchableFields(): array
    {
        return ['customer_id', 'status'];
    }

    public function searchableFieldTransformations(): array
    {
        return [
            'customer_id' => fn($value) => Customer::query()
                ->where('vat', 'like', "%{$value}%")
                ->orWhere('name', 'like', "%{$value}%")
                ->pluck('id')
                ->toArray(),
            'status' => function ($value) {
                $map = ['digitando' => 1, 'creada' => 2, 'aprobada' => 3, 'rechazada' => 4];
                $value = strtolower($value);
                return collect($map)
                    ->filter(fn($code, $label) => str_contains($label, $value))
                    ->values()
                    ->all();
            },
        ];
    }

    protected function customizeSortField(string $field)
    {
        return match($field) {
            'customer_id' => 'customers.vat',
            'status' => DB::raw('FIELD(status, 1,2,3,4)'),
            default => $field,
        };
    }

    protected function sortJoins(): array
    {
        return [ 'customers' => ['id', 'customer_id'] ];
    }

    protected function displayTransformations(): array
    {
        return [
            'status' => fn($v) => [1=>'DIGITANDO',2=>'CREADA',3=>'APROBADA',4=>'RECHAZADA'][$v] ?? '-',
            'total' => fn($v) => '$ ' . number_format((float)$v, 0, ',', '.'),
        ];
    }
}

// 📁 VISTA BLADE: resources/views/livewire/generic-crud.blade.php (fragmento de SELECT)

<div wire:ignore x-data="{ value: @entangle('fields.' . $field['name']) }"
     x-init="
         let select = $el.querySelector('select');
         $(select).select2({ width: '100%' });
         $(select).on('change', () => value = select.value);
         $watch('value', val => $(select).val(val).trigger('change'));
     "
>
    <select class="form-control w-100">
        <option value="">Seleccione...</option>
        @foreach ($field['options'] as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected($fields[$field['name']] == $optionValue)>
                {{ $optionLabel }}
            </option>
        @endforeach
    </select>
</div>
