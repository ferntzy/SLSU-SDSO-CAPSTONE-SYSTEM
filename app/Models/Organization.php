<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    use HasFactory;

    protected $table = 'organizations';
    protected $primaryKey = 'organization_id';
    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'organization_name',
        'organization_type',
        'adviser_name',
        'contact_email',
        'contact_number',
        'status',
        'description',
    ];

    public function officers()
    {
        return $this->hasMany(Officer::class, 'organization_id', 'organization_id');
    }

    // Link back to creator user
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // 🔗 Link to members
    public function members()
    {
        return $this->hasMany(Member::class, 'organization_id', 'organization_id');
    }
    // 🧮 Accessor for member count
    public function getMembersCountAttribute()
    {
        return $this->members()->count();
    }


    // Link to events
    public function events()
    {
        return $this->hasMany(Event::class, 'organization_id');
    }

    // Link to adviser user
    public function adviserUser()
    {
        return $this->belongsTo(User::class, 'adviser_name', 'user_id')
                    ->where('account_role', 'Faculty_Adviser');
    }

    // Accessor for adviser full name
    public function getAdvisorAttribute()
    {
        return $this->adviserUser?->profile?->full_name ?? 'N/A';
    }

    // Optional accessors
    public function getStatusAttribute($value)
    {
        return $value ?? 'Active';
    }

    public function getDescriptionAttribute($value)
    {
        return $value ?? '';
    }

    public function getNameAttribute()
    {
        return $this->organization_name;
    }

    public function getTypeAttribute()
    {
        return $this->organization_type;
    }
    public function studentOfficers()
    {
        return $this->hasManyThrough(
            UserProfile::class, // final table
            User::class,        // intermediate table
            'organization_id',  // FK on users table
            'user_id',          // FK on user_profiles table
            'organization_id',  // local key on organizations
            'user_id'           // local key on users
        )->where('account_role', 'Student_Organization');
    }

}

