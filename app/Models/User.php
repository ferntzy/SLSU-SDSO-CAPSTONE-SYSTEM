<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
  use HasApiTokens, HasFactory, Notifiable;

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */


  protected $table = 'users'; // table name
  protected $primaryKey = 'user_id'; // your actual PK name
  public $timestamps = true;
  protected $fillable = [
    'username',
    'password',
    'account_role',
    'profile_id'
  ];

  /**
   * The attributes that should be hidden for serialization.
   *
   * @var array<int, string>
   */
  protected $hidden = [
    'password',
    'remember_token',
  ];
  // public function organization()
  // {
  //   return $this->hasOne(Organization::class, 'user_id');
  // }

  public function organization()
  {
      return $this->belongsTo(Organization::class, 'organization_id');
  }


  /**
   * The attributes that should be cast.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'email_verified_at' => 'datetime',
    'password' => 'hashed',
  ];
  public function approvalTasks()
  {
    return $this->hasMany(EventApprovalFlow::class, 'approver_id', 'user_id');
  }

    public function profile()
    {
        return $this->belongsTo(UserProfile::class, 'profile_id', 'profile_id');
    }
    public function events()
    {
        return $this->hasMany(Event::class, 'organization_id', 'user_id');
    }
}
