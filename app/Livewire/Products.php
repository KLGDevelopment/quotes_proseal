<?php

namespace App\Livewire;

use App\Http\Controllers\OdooSyncController;
use App\Models\Product;
use App\Livewire\Base\CrudComponent;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Http;

#[Layout('layouts.livewire-adminlte')]
class Products extends CrudComponent
{
protected function displayTransformations(): array
{
    return [
        'type' => [
            'consu'   => 'PRODUCTO',
            'service' => 'SERVICIO',
        ],
    ];
}

    
    protected function model(): string
    {
        return Product::class;
    }
    
    protected function view(): string
    {
        return 'livewire.generic-crud'; // ← usa la vista genérica
    }
    
    public function title(): string
    {
        return 'Gestión de Productos';
    }
    
    protected function fields(): array
    {
        return [
            ['label' => 'ID',     'name' => 'id'],
            ['label' => 'Código',  'name' => 'code'],
            ['label' => 'Tipo',  'name' => 'type'],
            ['label' => 'Nombre','name' => 'name'],
        ];
    }
    
    protected function rules(): array
    {
        return [
            'fields.code' => 'required|string|unique:customers,code,' . ($this->itemId ?? 'NULL'),
            'fields.id' => 'required|integer|min:2',
            'fields.name' => 'nullable|string',
        ];
    }
    
    protected function searchableFields(): array
    {
        return ['id', 'code', 'name'];
    }

    protected function searchableFieldTransformations(): array
    {
        return [
            'type' => [
                'producto' => 'consu',
                'servicio' => 'service',
            ],
        ];
    }
    
    protected function odooSyncMethod(): string
    {
        return 'syncProducts';
    }
}
