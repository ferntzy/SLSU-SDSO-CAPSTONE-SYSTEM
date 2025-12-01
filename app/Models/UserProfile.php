<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    use HasFactory;

    protected $table = 'user_profiles';
    protected $primaryKey = 'profile_id';
    public $timestamps = true;

    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'contact_number',
        'address',
        'birthdate',
        'sex',
        'type',
        'email',
    ];

    // Relationship to user
    public function user()
    {
        return $this->hasOne(User::class, 'profile_id', 'profile_id');
    }

    // Relationship to organization
    // public function organization()   STUDENT PROFILE can belong to many organizations (but through members)
    // {
    //     return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    // }
     public function members()
    {
        return $this->hasMany(Member::class, 'profile_id', 'profile_id');
    }


    public function organizations()
    {
        return $this->belongsToMany(
            Organization::class,
            'members',
            'profile_id',
            'organization_id'
        );
    }


    public function advisedOrganizations()
    {
        return $this->hasMany(Organization::class, 'adviser_profile_id', 'profile_id');
    }


}
