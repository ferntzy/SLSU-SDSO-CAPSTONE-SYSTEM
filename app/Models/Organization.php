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
        'status',
        'description',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    // 🔗 The user account who created the organization
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    // New
    public function adviser()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id')
                    ->where('account_role', 'Faculty_Adviser');
    }


    // 🔗 The profile connected to the organization (Employee or Student)
    public function profile()
    {
        return $this->belongsTo(UserProfile::class, 'profile_id', 'profile_id');
    }

    // 🔗 Officers of this organization
    public function officers()
    {
        return $this->hasMany(Officer::class, 'organization_id', 'organization_id');
    }

    // 🔗 Members of this organization
    public function members()
    {
        return $this->hasMany(Member::class, 'organization_id', 'organization_id');
    }

    // 🔗 Events created by this organization
    public function events()
    {
        return $this->hasMany(Event::class, 'organization_id', 'organization_id');
    }


    /*
    |--------------------------------------------------------------------------
    | ACCESSORS / HELPERS
    |--------------------------------------------------------------------------
    */

    // Count members without extra queries in Blade
    public function getMembersCountAttribute()
    {
        return $this->members()->count();
    }

    // Default status if empty
    public function getStatusAttribute($value)
    {
        return $value ?: 'Active';
    }

    // Return empty string if no description
    public function getDescriptionAttribute($value)
    {
        return $value ?: '';
    }

    // Shortcut accessor for organization name
    public function getNameAttribute()
    {
        return $this->organization_name;
    }

    // Shortcut accessor for type
    public function getTypeAttribute()
    {
        return $this->organization_type;
    }

}
