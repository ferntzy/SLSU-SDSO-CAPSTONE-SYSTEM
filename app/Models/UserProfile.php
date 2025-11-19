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
        'contact_number',
        'address',
        'birthdate',
        'sex',
        'type',
        'profile_picture_path',
        'email',
        'organization_id',
    ];

    protected $casts = [
        'birthdate' => 'date',
    ];

    // Relationship to user
    public function user()
    {
        return $this->hasOne(User::class, 'profile_id', 'profile_id');
    }

    // Relationship to organization
    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }
}
