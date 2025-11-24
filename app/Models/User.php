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

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'user_id', 'user_id');
    }

    public function approvalTasks()
    {
        return $this->hasMany(EventApprovalFlow::class, 'approver_id', 'user_id');
    }

    public function user_profile()
    {
        return $this->belongsTo(UserProfile::class, 'profile_id');
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
