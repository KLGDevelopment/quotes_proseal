<?php

namespace App\Livewire;

use App\Http\Controllers\OdooSyncController;
use App\Models\Product;
use App\Livewire\Base\CrudComponent;
use App\Models\BranchOffice;
use App\Models\Division;
use App\Models\Equipment;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Http;

#[Layout('layouts.livewire-adminlte')]
class Equipments extends CrudComponent
{
    protected function displayTransformations(): array
    {
        return [
          
        ];
    }

    
    protected function model(): string
    {
        return Equipment::class;
    }
    
    protected function view(): string
    {
        return 'livewire.generic-crud'; // ← usa la vista genérica
    }
    
    public function title(): string
    {
        return 'Equipos';
    }
    
    protected function fields(): array
    {
        return [
            ['label' => 'ID',     'name' => 'id', 'sortable' => true, 'show_in_form' => false],
            ['label' => 'Marca','name' => 'brand', 'sortable' => true],
            ['label' => 'Modelo','name' => 'model', 'sortable' => true],
            ['label' => 'Tamaño','name' => 'size', 'sortable' => true],
        ];
    }
    
    protected function rules(): array
    {
        return [
            'fields.brand' => 'nullable|string',
            'fields.model' => 'nullable|string',
            'fields.size' => 'nullable|string',
            'fields.id' => 'required|integer|min:2',
        ];
    }
    
    protected function searchableFields(): array
    {
        return ['id', 'brand','model','size'];
    }

    protected function searchableFieldTransformations(): array
    {
        return [
            
        ];
    }
    
    protected function odooSyncMethod(): string
    {
        return 'syncDivisions';
    }
}
