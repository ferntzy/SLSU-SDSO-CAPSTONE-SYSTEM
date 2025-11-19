<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Signature extends Model
{
  protected $primaryKey = 'signature_id';
  protected $fillable = ['user_id', 'signature_path'];

  public function user()
  {
    return $this->belongsTo(User::class, 'user_id', 'user_id');
  }
}
