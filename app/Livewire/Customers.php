<?php

namespace App\Livewire;

use App\Http\Controllers\OdooSyncController;
use App\Models\Customer;
use App\Livewire\Base\CrudComponent;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Http;

#[Layout('layouts.livewire-adminlte')]
class Customers extends CrudComponent
{

    
    protected function model(): string
    {
        return Customer::class;
    }
    
    protected function view(): string
    {
        return 'livewire.generic-crud'; // ← usa la vista genérica
    }
    
    public function title(): string
    {
        return 'Gestión de Clientes';
    }
    
    protected function fields(): array
    {
        return [
            ['label' => 'RUT',     'name' => 'vat'],
            ['label' => 'Nombre',  'name' => 'name'],
            ['label' => 'Teléfono','name' => 'phone'],
        ];
    }
    
    protected function rules(): array
    {
        return [
            'fields.vat' => 'required|string|unique:customers,vat,' . ($this->itemId ?? 'NULL'),
            'fields.name' => 'required|string|min:2',
            'fields.phone' => 'nullable|string',
        ];
    }
    
    protected function searchableFields(): array
    {
        return ['vat', 'name', 'phone'];
    }
    
    protected function odooSyncMethod(): string
    {
        return 'syncCustomers';
    }
}
