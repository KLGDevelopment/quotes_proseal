<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class QuoteDetailMaster extends Model
{
    
    protected $fillable = ['quote_line_id', 'product_id', 'quantity', 'unit_price','sale_value', 'profit_margin']; // incluir 'status'
    
    public function getData($attribute){
        switch($attribute){
            default: return $this->$attribute;break;
            case "product_id": return "HOLA";break;
       }
    }

    public function product()
    {
        return $this->belongsTo(\App\Models\Product::class, 'product_id');
    }

    

    public function quoteLine()
    {
        return $this->belongsTo(\App\Models\QuoteLine::class,'quote_line_id');
    }
}
