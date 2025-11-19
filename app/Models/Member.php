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

    protected $fillable = [
        'organization_id',
        'member_name',
        'contact_email',
        'contact_number',
        'membership_status',
        'joined_at',
    ];

    /**
     * Each member belongs to an organization
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

    /**
     * Optional: Scope for active members
     */
    public function scopeActive($query)
    {
        return $query->where('membership_status', 'active');
    }
}
