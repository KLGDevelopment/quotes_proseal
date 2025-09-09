<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Quote extends Model 
{
  public $guarded = [];
  //protected $fillable = ['customer_id', 'total', 'status', 'neto','service_description','money_type','branch_office_id','division_id']; // incluir 'status'
  
  static $moneyArray = [0 => 'CLP', 1 => 'USD', 2 => 'UF'];
  static $statusOptions = [
    0 => 'DIGITANDO',
    1 => 'CREADA',
    2 => 'APROBADA',
    3 => 'RECHAZADA',
  ];
  
  public function getData($attribute){
    switch($attribute){
      default: return $this->attribute;break;
      case "status": return self::$statusOptions[$this->status];break;
    }
  }
  
  public function customer()
  {
    return $this->belongsTo(\App\Models\Customer::class, 'customer_id');
  }
  
  public function details()
  {
    return $this->hasMany(\App\Models\QuoteDetail::class, 'quote_id');
  }

  public function quoteDetails()
  {
    return $this->hasMany(\App\Models\QuoteDetail::class, 'quote_id');
  }
  
  public function branchOffice()
  {
    return $this->belongsTo(\App\Models\BranchOffice::class, 'branch_office_id');
  }
  
  public function division()
  {
    return $this->belongsTo(\App\Models\Division::class, 'division_id');
  }
}
