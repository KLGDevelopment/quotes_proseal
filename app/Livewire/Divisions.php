<?php

namespace App\Livewire;

use App\Http\Controllers\OdooSyncController;
use App\Models\Product;
use App\Livewire\Base\CrudComponent;
use App\Models\BranchOffice;
use App\Models\Division;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Http;

#[Layout('layouts.livewire-adminlte')]
class Divisions extends CrudComponent
{
    protected function displayTransformations(): array
    {
        return [
          
        ];
    }

    
    protected function model(): string
    {
        return Division::class;
    }
    
    protected function view(): string
    {
        return 'livewire.generic-crud'; // ← usa la vista genérica
    }
    
    public function title(): string
    {
        return 'Divisiones';
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
            'fields.name' => 'nullable|string',
            'fields.id' => 'required|integer|min:2',
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
        return 'syncDivisions';
    }
}
