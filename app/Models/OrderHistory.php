<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderHistory extends Model 
{
  protected $fillable=['order_id','changed_by','from_status','to_status','note'];
  public function order(){ return $this->belongsTo(Order::class); }
  public function user(){ return $this->belongsTo(User::class,'changed_by'); }
}
