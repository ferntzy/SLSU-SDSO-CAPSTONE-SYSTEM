<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    use HasFactory;

    protected $table = 'members';
    protected $primaryKey = 'member_id';

    public $timestamps = true; // uses created_at and updated_at

   protected $fillable = ['organization_id', 'profile_id',];

    /**
     * Each member belongs to an organization
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }
    public function profile()
    {
      return $this->belongsTo(UserProfile::class, 'profile_id', 'profile_id');
    }
    public function userProfile()
{
    return $this->belongsTo(UserProfile::class, 'profile_id');
}
   public function officer()
    {
        return $this->hasOne(Officer::class, 'members_id', 'member_id');
    }



    public function scopeActive($query)
    {
        return $query->where('membership_status', 'active');
    }
}
