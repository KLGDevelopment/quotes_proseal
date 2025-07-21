<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    public $guarded = [];
    protected $fillable = ['customer_id','name','email','phone','odoo_customer_id'];
  
}
