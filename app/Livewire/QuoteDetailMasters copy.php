<?php
namespace App\Livewire;

use App\Models\Quote;
use App\Models\QuoteDetail;
use App\Models\Product;
use App\Models\QuoteDetailMaster;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.livewire-adminlte')]
class QuoteDetailMasters extends Component
{
    public $quote;
    public $detailQuote;
    
    public $cartItems = [];
    public $productId, $quantity, $amount, $profit_margin;
    public $isEditing = false, $editingId = null;
    public $showForm = false;

    protected $listeners = ['deleteConfirmed'];
    
    public function mount($quoteId, $detailQuoteId)
    {
        $this->quote = Quote::find($quoteId);
        $this->detailQuote = QuoteDetail::find($detailQuoteId);
        $this->loadCart();
    }
    
    public function loadCart()
    {
        $this->cartItems = QuoteDetailMaster::with('product')
        ->where('quote_detail_id', $this->detailQuote->id)
        ->get();
    }
    
    public function openForm()
    {
        $this->resetForm();
        $this->showForm = true;
    }
    
    public function edit($id)
    {
        $item = QuoteDetailMaster::findOrFail($id);
        $this->productId = $item->product_id;
        $this->quantity = $item->quantity;
        $this->amount = $item->amount;
        $this->profit_margin = $item->profit_margin;
        $this->editingId = $id;
        $this->isEditing = true;
        $this->showForm = true;
    }
    
    public function deleteConfirmed($id)
    {
        QuoteDetailMaster::findOrFail($id)->delete();
        $this->loadCart();
    }
    
    public function save()
    {
        $sale_value = ($this->amount + ($this->amount * $this->profit_margin / 100)) * $this->quantity;
        
        $data = [
            'quote_detail_id' => $this->detailQuote->id,
            'product_id' => $this->productId,
            'quantity' => $this->quantity,
            'amount' => $this->amount,
            'profit_margin' => $this->profit_margin,
            'sale_value' => $sale_value,
        ];
        
        if ($this->isEditing) {
            QuoteDetailMaster::find($this->editingId)->update($data);
        } else {
            QuoteDetailMaster::create($data);
        }
        
        $this->resetForm();
        $this->loadCart();
        $this->showForm = false;
    }
    
    public function resetForm()
    {
        $this->productId = $this->quantity = $this->amount = $this->profit_margin = null;
        $this->isEditing = false;
        $this->editingId = null;
    }
    
    public function render()
    {
        return view('livewire.quote-detail-master');
    }
}
