<?php
namespace App\Livewire;

use App\Livewire\Base\CrudComponent;
use App\Models\Product;
use App\Models\Quote;
use App\Models\QuoteDetail;
use App\Models\QuoteDetailMaster;
use App\Models\QuoteLine;
use Livewire\Attributes\Layout;

#[Layout('layouts.livewire-adminlte')]
class QuoteLines extends CrudComponent
{
    // VARIABLE SEGÚN MODELO
    public QuoteDetail $quoteDetail;

    // SIEMPRE FIJO
    protected ?string $sortableField = 'order';
    protected string $parentKey = 'quote_detail_id';
    protected bool $afterSave = true;
    public $masterLink;
    public $backLink;
    
    public $quoteId;
    public $quoteLineId;
    public $quoteDetailId;
    public $quote;

    public function title(): string
    {
        return 'Lineas de Detalle';
    }

    public function hydrate()
    {
        if (!isset($this->quote) && $this->quoteId) {
            $this->quote = Quote::find($this->quoteId);
        }

        if (!isset($this->quoteDetail) && $this->quoteDetailId) {
            $this->quoteDetail = QuoteDetail::find($this->quoteDetailId);
        }

        if (!isset($this->parentId)) {
            $this->parentId = $this->quoteDetailId;
        }

    }

    public function mount($quoteId = null,$quoteDetailId = null)
    {
        // VARIABLE SEGÚN MODELO, DEBE IR MODELO PADRE Y MODELO HIJO
        $this->quoteId = $quoteId;
        $this->quoteDetailId = $quoteDetailId;
        $this->quote = Quote::findOrFail($quoteId);
        $this->quoteDetail = QuoteDetail::findOrFail($quoteDetailId);


        // DEPENDE SI SINCRONIZA CON ODOO
        $this->showSyncButton = false;
        
        // EN CASO DE SER MODELO HIJO DEBE IR ID DEL PADRE
        parent::mount($quoteDetailId);
        $this->backLink = "/quotes/$this->quoteId/details";
        $this->masterLink = "/quotes/$this->quoteId/details/$this->quoteDetailId/lines/%%rowId%%/master";
        
        
    }
    
    // VARIALBE SEGÚN MODELO
    protected function model(): string { return QuoteLine::class; }
    
    // VARIABLE SEGÚN BLADE DEL CONTROLADOR DE LIVEWIRE
    protected function view(): string { return 'livewire.quote-lines'; }
    
    
    protected function searchableFields(): array
    {
        return ['product_id']; // puedes ajustar o agregar más campos si lo deseas
    }
    
    protected function baseQuery()
    {
        return QuoteLine::where('quote_detail_id', $this->parentId);
    }
    
    protected function fields(): array
    {
        return [
                   
            [
                'label' => 'Producto',
                'name'  => 'product_id',
                'type'  => 'select',
                'sortable' => true,
                'options' => Product::where('code','LIKE','COT-%')->get()->mapWithKeys(fn($c) => [$c->id => "{$c->code} - {$c->name}"])->toArray()
            ],
            ['label' => 'Descripción', 'name' => 'description', 'type' => 'text', 'class' => 'text-center'],
            ['label' => 'Cantidad', 'name' => 'quantity', 'type' => 'input', 'class' => 'text-center'],
            ['label' => 'Valor Unitario', 'name' => 'unit_price', 'type' => 'input', 'format' => 'currency', 'class' => 'text-right', 'show_in_form' => true, 'read_only' => true, 'default' => 0],
            ['label' => 'Margen de Utilidad', 'name' => 'profit_margin', 'type' => 'input', 'class' => 'text-center', 'read_only' => true, 'format' => 'percentage', 'default' => 0],
            ['label' => 'Subtotal Venta', 'name' => 'sale_value', 'type' => 'input', 'format' => 'currency', 'class' => 'text-right', 'show_in_form' => true, 'calculated' => true, 'if_less_than' => ['unit_price','bg-danger']],
            
        ];
    }
    
    protected function rules(): array
    {
        return [
            'fields.profit_margin' => 'required|numeric',
            'fields.quantity' => 'required|numeric',
            'fields.sale_value' => 'required|numeric',
        ];
    }

    public function searchableFieldTransformations(): array
    {
        return [
            'product_id' => fn($value) => Product::query()
                ->where('code', 'like', "%{$value}%")
                ->orWhere('name', 'like', "%{$value}%")
                ->pluck('id')
                ->toArray()

        ];
    }

    public function save()
    {
        
        /**
         * Si se está editando un QuoteLine, el precio de venta no puede ser menor que el unit_price.
         * Si lo que se está agregando es un precio de venta y es mayor que el unit_price, se debe calcular el profit_margin.
         * Si se cambia el profit_margin, se debe calcular el sale_value.
         *  */ 
        if ($this->itemId) {
            $quoteLine = QuoteLine::find($this->itemId);
            if ($this->fields['sale_value'] < $quoteLine->unit_price) {
                $this->addError('fields.sale_value', 'El precio de venta no puede ser menor que el valor unitario.');
                return;
            }

            


        } else {
            // Si es un nuevo QuoteLine, se debe calcular el sale_value

        }
   
        parent::save();
        
        $this->afterSaveHook();
        $this->resetForm();
    }


    public function afterSaveHook()
    {
        /**
         * Esta función se ejecuta después de guardar un QuoteLine.
         * Actualiza el total de venta del QuoteLine, el QuoteDetail y el Quote.
         * Se debe realizar la siguiente condición:
         * - Si existe un margen de utilidad, se debe calcular el total de venta del QuoteLine con el nuevo valor unitario.
         * - Si no existe un margen de utilidad y existe un sale_value y el nuevo sale_value es menor que el unit_price, se debe calcular el total de venta del QuoteLine con el nuevo valor unitario y calcular el margen de utilidad en relación al sale_value antiguo.
         */
     
        // Sumar el total de venta de todos los productos del detalle
        $totalSale = QuoteDetailMaster::where('quote_line_id', $this->itemId)->sum('sale_value');
 
        $quoteLine = QuoteLine::find($this->itemId);
        
 

        if ($quoteLine->unit_price > 0) {
            $profitMargin = round(($this->fields['sale_value'] - $quoteLine->unit_price) / $quoteLine->unit_price * 100, 2);
        }else {
            $profitMargin = 0;
        }

         $quoteLine->update([
            'profit_margin' => $profitMargin
        ]);
        

        // Total de todas las lineas
        $totalLineSale = QuoteLine::where('quote_detail_id',$this->quoteDetailId)->sum('sale_value');
   
        // Actualizar el monto total del detalle de cotización
        $this->quoteDetail->update([
            'sale_value' => $totalLineSale
        ]);
 
        $totalQuote = QuoteDetail::where('quote_id',$this->quoteId)->sum('sale_value');

        $this->quote->update([
            'neto' => $totalQuote
        ]);

        // (Opcional) Log para depuración
        logger()->info("Actualizado monto en QuoteDetail #{$this->quoteDetailId}: {$totalSale}");
        $this->resetForm();
    }
}
