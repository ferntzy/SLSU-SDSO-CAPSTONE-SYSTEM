<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class UserLog extends Model
{
  use HasFactory;

protected $fillable = ['user_id', 'username', 'action', 'ip_address', 'user_agent'];

  public function user()
  {
    return $this->belongsTo(User::class, 'user_id', 'user_id');
  }
}
