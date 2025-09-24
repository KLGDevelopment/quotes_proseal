<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class QuoteLine extends Model
{
    protected $fillable = ['quote_detail_id', 'product_id', 'quantity', 'sale_value', 'unit_price','profit_margin', 'description']; // incluir 'status'


    public function getData($attribute){
        switch($attribute){
            default: return $this->$attribute;break;
        }
    }
    
    public function product()
    {
        return $this->belongsTo(\App\Models\Product::class, 'product_id');
    }

    public function quoteDetailMaster()
    {
        return $this->hasMany(\App\Models\QuoteDetailMaster::class, 'quote_line_id');
    }
}
