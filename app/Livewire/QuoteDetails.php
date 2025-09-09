<?php
namespace App\Livewire;

use App\Livewire\Base\CrudComponent;
use App\Models\Quote;
use App\Models\QuoteDetail;
use Livewire\Attributes\Layout;
use \Illuminate\Support\Facades\Auth;

#[Layout('layouts.livewire-adminlte')]
class QuoteDetails extends CrudComponent{

    

    public function approveBasic($id)
    {
  
        if (auth()->user()->hasRole('approver-basic') && $this->quote->status === 0) {
            $this->quote->status = 1; // CREADA
            $this->quote->save();
            session()->flash('success', 'Cotización aprobada por básico.');
        }
    }

    public function approveAdvanced($id)
    {
        if (auth()->user()->hasRole('approver-advanced') && $this->quote->status === 1) {
            $this->quote->status = 2; // APROBADA
            $this->quote->save();
            session()->flash('success', 'Cotización aprobada por avanzado.');
        }
    }

    public Quote $quote;
    protected ?string $sortableField = 'order';
    public $masterLink;
    public $backLink;    

    public array $extraButtons = [];

    public function mount($parentId = null)
    {
        $this->quote = Quote::with('customer')->findOrFail($parentId);
        $this->showSyncButton = false;
        parent::mount($parentId);
        $this->masterLink = "/quotes/".$parentId."/details/%%rowId%%/lines";
        $this->backLink = "/quotes";

        $this->extraButtons = [
            [
                'text' => 'Previsualizar Cotización',
                'icon' => 'fas fa-file-pdf',
                'color' => 'info',
                'link' => "/quotes/{$this->quote->id}/pdf",
                'target_blank' => true,
            ],
            [
                'text' => 'Clonar Cotización',
                'icon' => 'fas fa-clone',
                'color' => 'warning',
                'link' => "/quotes/{$this->quote->id}/clone",
                 'target_blank' => false,
            ],
        ];
    }
    
    protected function model(): string { return QuoteDetail::class; }
    
    protected function view(): string { return 'livewire.quote-detail'; }
    
    
    protected function searchableFields(): array
    {
        return ['item']; // puedes ajustar o agregar más campos si lo deseas
    }
    
    public function title(): string
    {
        return 'Detalles de Cotización';
    }
    
    protected function baseQuery()
    {
        return QuoteDetail::where('quote_id', $this->parentId);
    }
    
    protected function fields(): array
    {
        return [
            ['label' => 'Órden', 'name' => 'order', 'type' => 'input', 'sortable' => true],
            ['label' => 'Ítem', 'name' => 'item', 'type' => 'input', 'sortable' => true],
          //  ['label' => 'Cantidad', 'name' => 'quantity', 'type' => 'input',  'class' => 'text-right', 'sortable' => true, 'show_in_form' => true],
            //['label' => 'Precio', 'name' => 'amount', 'type' => 'input', 'format' => 'currency', 'class' => 'text-right', 'sortable' => true, 'show_in_form' => false],
        ];
    }
    
    protected function rules(): array
    {
        return [
            'fields.item' => 'required|string',
            //'fields.amount' => 'required|numeric',
        ];
    }
    
    public function sentToOdoo(): void
    {

        $this->isSyncing = true;

        try {
           
            $controller = app(\App\Http\Controllers\OdooSyncController::class);
    
            $response = $controller->syncSaleOrder($this->quote);
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



}
