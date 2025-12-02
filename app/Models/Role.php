<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
  protected $table = 'role'; // ✅ IMPORTANT — table name must match what exists
  protected $primaryKey = 'id';
  public $timestamps = false;

  protected $fillable = [
    'RoleName',
    'Frequency',
    'order',
  ];


  public function officers()
    {
        return $this->hasMany(Officer::class, 'role_id', 'id');
    }

}
