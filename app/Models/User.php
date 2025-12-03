<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
  use HasApiTokens, HasFactory, Notifiable;

  protected $table = 'users';
  protected $primaryKey = 'user_id';   // custom PK
  public $incrementing = true;          // <--- ADD THIS
  protected $keyType   = 'int';         // <--- ADD THIS
  public $timestamps   = true;

  protected $fillable = [
    'username',
    'password',
    'account_role',
    'profile_id',
    'officers_id',
    'signature',
  ];

  protected $hidden = [
    'password',
    'remember_token',
  ];

  protected $casts = [
    'email_verified_at' => 'datetime',
    'password' => 'hashed',
  ];

  // public function organization()
  // {
  //     return $this->belongsTo(Organization::class, 'user_id', 'user_id');
  // }

  public function approvalTasks()
  {
    return $this->hasMany(EventApprovalFlow::class, 'approver_id', 'user_id');
  }

  public function profile()
  {
    return $this->belongsTo(UserProfile::class, 'profile_id', 'profile_id');
  }

  // public function getProfileIdAttribute()
  // {
  //   return $this->profile_id;
  // }
  public function officer()
  {
    return $this->belongsTo(Officer::class, 'officers_id', 'officer_id');
  }

  public function advisedOrganizations()
  {
    return $this->hasMany(\App\Models\Organization::class, 'adviser_id');
  }

  public function advisedOrganization() // optional, for backward compatibility
  {
    return $this->hasOne(\App\Models\Organization::class, 'adviser_id');
  }

public function user_profile()
  {
    return $this->belongsTo(\App\Models\UserProfile::class, 'profile_id', 'profile_id');
  }

  public function notifications()
  {
    return $this->hasMany(\App\Models\CustomNotification::class, 'user_id', 'user_id');
  }

  public function unreadNotifications()
  {
    return $this->notifications()->where('status', 'unread');
  }

  public function readNotifications()
  {
    return $this->notifications()->where('status', 'read');
  }
  public function getPendingPermitCountAttribute()
  {
    return \DB::table('notifications')
      ->where('user_id', $this->user_id)
      ->where('status', 'unread')
      ->where('notification_type', 'event_approval') // or 'permit_request', etc.
      ->count();
  }
  public function permits()
{
    return $this->hasMany(Permit::class, 'student_id'); // adjust foreign key as needed
    // Or if permit belongs to a profile/user via user_id:
    // return $this->hasMany(Permit::class, 'user_id');
}
  // public function events()
  // {
  //     return $this->hasMany(Event::class, 'organization_id', 'user_id');
  // }
  public function organization()
{
    return $this->belongsTo(Organization::class);
}
}
