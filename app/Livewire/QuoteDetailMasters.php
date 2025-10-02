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
class QuoteDetailMasters extends CrudComponent
{
    // VARIABLE SEGÚN MODELO
    public QuoteLine $quoteLine;

    // SIEMPRE FIJO
    protected ?string $sortableField = 'order';
    protected string $parentKey = 'quote_line_id';
    public $masterLink;
    public $backLink;
    
    public $quoteId;
    public $quoteDetailId;
    public $quoteDetail;
    public $quoteLineId;


    public function title(): string
    {
        return 'Maestra del Detalle de Cotización';
    }

    public function hydrate()
    {
        if (!isset($this->quote) && $this->quoteId) {
            $this->quote = Quote::find($this->quoteId);
        }

        if (!isset($this->quoteDetail) && $this->quoteDetailId) {
            $this->quoteDetail = QuoteDetail::find($this->quoteDetailId);
        }

        if (!isset($this->quoteLine) && $this->quoteLineId) {
            $this->quoteLine = QuoteLine::find($this->quoteLineId);
        }

        if (!isset($this->parentId)) {
            $this->parentId = $this->quoteLineId;
        }
    }

    public function mount($quoteId = null,$quoteDetailId = null, $quoteLineId = null)
    {
  
        // VARIABLE SEGÚN MODELO, DEBE IR MODELO PADRE Y MODELO HIJO
        $this->quoteId = $quoteId;
        $this->quoteDetailId = $quoteDetailId;
        $this->quote = Quote::findOrFail($quoteId);
        $this->quoteDetail = QuoteDetail::findOrFail($quoteDetailId);
        $this->quoteLine = QuoteLine::findOrFail($quoteLineId);
     
        // DEPENDE SI SINCRONIZA CON ODOO
        $this->showSyncButton = false;
        
        // EN CASO DE SER MODELO HIJO DEBE IR ID DEL PADRE
        parent::mount($quoteLineId);
        $this->backLink = "/quotes/$this->quoteId/details/$this->quoteDetailId/lines";
        
        $this->breadcrumbs = [
            ['label' => 'Cotización'],
            ['label' => 'Sección'],
            ['label' => 'Actividad'],
            ['label' => 'Detalle Maestro'],
        ];        
    }
    
    // VARIALBE SEGÚN MODELO
    protected function model(): string { return QuoteDetailMaster::class; }
    
    // VARIABLE SEGÚN BLADE DEL CONTROLADOR DE LIVEWIRE
    protected function view(): string { return 'livewire.quote-detail-master'; }
    
    
    protected function searchableFields(): array
    {
        return ['product_id']; // puedes ajustar o agregar más campos si lo deseas
    }
    
    public function baseQuery()
    {
        return QuoteDetailMaster::with('product')->where('quote_line_id', $this->parentId);
    }
    
    protected function fields(): array
    {
        return [
           
            /*
            [
                'label' => 'Producto',
                'name'  => 'product_id',
                'type'  => 'select',
                'sortable' => true,
                'options' => Product::whereNot('code','LIKE','COT-%')->get()->mapWithKeys(fn($c) => [$c->id => "{$c->code} - {$c->name}"])->toArray()
            ]*/
            [
                'name' => 'product_id',
                'label' => 'Producto',
                'sortable'  => true,
                'ajax' => true,
                'model' => \App\Models\Product::class, // Modelo externo
                'display_column' => 'name',
                'display_format' => '{code} - {name}', // ← mostrar ambos
                'ajax_url' => '/api/products',
            ],

            ['label' => 'Cantidad', 'name' => 'quantity', 'type' => 'input', 'class' => 'text-center'],
            ['label' => 'Costo Unitario', 'name' => 'unit_price', 'type' => 'input', 'format' => 'currency', 'class' => 'text-right'],
          //  ['label' => 'Margen de Utilidad', 'name' => 'profit_margin', 'type' => 'input', 'class' => 'text-center'],
            ['label' => 'Costo Total', 'name' => 'sale_value', 'type' => 'input', 'format' => 'currency', 'class' => 'text-right', 'show_in_form' => false, 'calculated' => true],
            
        ];
    }
    
    protected function rules(): array
    {
        return [
            'fields.profit_margin' => 'required|numeric',
            'fields.quantity' => 'required|numeric',
            'fields.unit_price' => 'required|numeric',
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
        if (
          //  isset($this->fields['quantity'], $this->fields['unit_price'], $this->fields['profit_margin'])
            isset($this->fields['quantity'], $this->fields['unit_price'])
        ) {
            $q = (float) $this->fields['quantity'];
            $a = (float) $this->fields['unit_price'];
           // $m = (float) $this->fields['profit_margin'];
           
           // $this->fields['sale_value'] = $q * $a * (1 + ($m / 100));
            $this->fields['sale_value'] = $q * $a;
        }

        parent::save(); // ejecuta la lógica genérica de guardar
        $this->afterSaveHook(); 


    }

    public function afterSaveHook()
    {
        /**
         * Sumar todos los sale_value de venta en QuoteDetailMaster que dependen del QuoteLine padre y
         * actualizar el amount de QuoteLine padre
         */
        // Sumar el total de venta de todos los productos del detalle

        $unitPriceLine = QuoteDetailMaster::where('quote_line_id', $this->quoteLineId)->sum('sale_value');
        $qLine = $this->quoteLine->quantity;
        
        $this->quoteLine->update([
            'unit_price' => $unitPriceLine
            //'sale_value' => $qLine*$unitPriceLine
        ]);

        /**
         * Sumar todos los amount en QuoteLine que dependen del QuoteDetail padre y
         * actualizar el amount de QuoteDetail padre
         */

        $saleValueDetail = QuoteLine::where('quote_detail_id', $this->quoteDetail->id)->sum('sale_value');

        // Actualizar el monto total del detalle de cotización
        $this->quoteDetail->update([
            'sale_value' => $saleValueDetail
        ]);
 
        /**
         * Sumar todos los sale_value en QuoteDetail que dependen del Quote padre y
         * actualizar el neto del Quote padre
         */

        $totalQuoteDetailSaleValue = QuoteLine::where('quote_detail_id',$this->quoteDetailId)->sum('sale_value');
        //$totalQuote = QuoteDetail::where('quote_id',$this->quoteId)->sum('amount');

        $this->quote->update([
            'neto' => $totalQuoteDetailSaleValue
        ]);

        // (Opcional) Log para depuración
        //logger()->info("Actualizado monto en QuoteDetail #{$this->quoteDetailId}: {$totalSale}");
    }

    protected function displayTransformations(): array
    {
        return [
            'product_id' => function ($value) {
                $product = \App\Models\Product::find($value);
                return $product ? "{$product->code} - {$product->name}" : (string) $value;
            },
        ];
    }
}
