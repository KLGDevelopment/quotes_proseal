<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuoteDetail extends Model
{
    protected $fillable = ['order', 'item', 'sale_value','quote_id', 'quantity']; // incluir 'status'


    public $guarded = [];
    
    public function getData($attribute){
        switch($attribute){
        default: return $this->$attribute;break;
            case "amount": return '$ ' . number_format((float) $this->$attribute, 0, ',', '.');
       }
    }
    
    public function customer()
    {
        return $this->belongsTo(\App\Models\Customer::class, 'customer_id');
    }
    
    public function quote()
    {
        return $this->belongsTo(\App\Models\Quote::class);
    }
    
    public function lines()
    {
        return $this->hasMany(\App\Models\QuoteLine::class, 'quote_detail_id');
    }

    public function quoteLines()
    {
        return $this->hasMany(\App\Models\QuoteLine::class, 'quote_detail_id');
    }

    public function calculateAmount()
    {
        $total = QuoteLine::where('quote_detail_id', $this->id)->sum('sale_value');

        return $total;
    }

    // Métodos de aprobación deben ir en el componente Livewire, no en el modelo.
}
