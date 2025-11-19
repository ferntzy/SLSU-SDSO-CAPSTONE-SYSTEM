<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Officer extends Model
{
    use HasFactory;

    protected $table = 'officers';
    protected $primaryKey = 'officer_id';
    protected $fillable = [
        'organization_id',
        'user_id',
        'role',
        'officer_name',
        'contact_email',
        'contact_number',
        'member_id'
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
    public function profile()
    {
        return $this->hasOneThrough(
            UserProfile::class, // final target
            User::class,        // intermediate model
            'user_id',          // FK on User table
            'user_id',          // FK on UserProfile table
            'user_id',          // local key on Officer table
            'user_id'           // local key on User table
        );
    }
}

