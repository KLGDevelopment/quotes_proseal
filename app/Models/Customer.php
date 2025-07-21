<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    public $guarded = [];
    protected $fillable = ['name','vat','phone','odoo_id'];
}
