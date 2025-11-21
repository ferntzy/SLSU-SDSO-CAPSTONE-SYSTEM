<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Officer extends Model
{
    use HasFactory;

    protected $primaryKey = 'officer_id';
    protected $fillable = [
        'user_id',
        'profile_id',
        'organization_id',
        'role',
    ];

    // Link back to the organization
    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

    // Link to user (optional)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    // Link to profile
    public function profile()
    {
        return $this->belongsTo(UserProfile::class, 'profile_id', 'profile_id');
    }
}
