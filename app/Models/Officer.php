<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Officer extends Model
{
    protected $table = 'officers';
    protected $primaryKey = 'officer_id';

    protected $fillable = [
        'organization_id',
        'member_id',     // FIXED
        'role_id',        // FIXED
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

      public function member()
    {
        return $this->belongsTo(Member::class, 'members_id', 'member_id');
    }

    public function profile()
    {
        return $this->belongsTo(UserProfile::class, 'profile_id', 'profile_id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id', 'id');
    }

      public function userAccount()
    {
        return $this->hasOne(User::class, 'officers_id', 'officer_id');
    }
}

