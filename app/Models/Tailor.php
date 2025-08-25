<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tailor extends Model 
{
  protected $fillable = ['name','phone','note','user_id'];
  public function user(){ return $this->belongsTo(User::class); }
}