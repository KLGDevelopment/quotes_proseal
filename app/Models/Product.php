<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    //
    protected $fillable = ['code', 'name', 'created_at', 'updated_at', 'type', 'category_id', 'odoo_id','odoo_category_id'];


}
