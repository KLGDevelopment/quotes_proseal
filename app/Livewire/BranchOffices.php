<?php

namespace App\Livewire;

use App\Http\Controllers\OdooSyncController;
use App\Models\Product;
use App\Livewire\Base\CrudComponent;
use App\Models\BranchOffice;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Http;

#[Layout('layouts.livewire-adminlte')]
class BranchOffices extends CrudComponent
{
    protected function displayTransformations(): array
    {
        return [
          
        ];
    }

    
    protected function model(): string
    {
        return BranchOffice::class;
    }
    
    protected function view(): string
    {
        return 'livewire.generic-crud'; // ← usa la vista genérica
    }
    
    public function title(): string
    {
        return 'Sucursales';
    }
    
    protected function fields(): array
    {
        return [
            ['label' => 'ID',     'name' => 'id', 'sortable' => true],
            
            ['label' => 'Nombre','name' => 'name', 'sortable' => true],
        ];
    }
    
    protected function rules(): array
    {
        return [
            
            'fields.id' => 'required|integer|min:2',
            'fields.name' => 'nullable|string',
        ];
    }
    
    protected function searchableFields(): array
    {
        return ['id', 'name'];
    }

    protected function searchableFieldTransformations(): array
    {
        return [
            
        ];
    }
    
    protected function odooSyncMethod(): string
    {
        return 'syncBranchOffices';
    }
}
