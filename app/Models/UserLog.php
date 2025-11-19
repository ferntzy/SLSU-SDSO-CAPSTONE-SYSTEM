<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class UserLog extends Model
{
  use HasFactory;

  protected $fillable = ['user_id', 'action', 'ip_address'];

  public function user()
  {
    // 👇 link using user_id (not id)
    return $this->belongsTo(User::class, 'user_id', 'user_id');
  }
}
