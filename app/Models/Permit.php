<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Vinkla\Hashids\Facades\Hashids;

class Permit extends Model
{
  protected $table = 'permits';
  protected $primaryKey = 'permit_id';
  public $timestamps = true;
  protected $appends = ['hashed_id'];
  protected $fillable = [
    'organization_id',
    'title_activity',
    'purpose',
    'type',
    'nature',
    'venue',
    'date_start',
    'date_end',
    'time_start',
    'time_end',
    'participants',
    'number',
    'signature_data',
    'signature_upload',
    'pdf_data',
    'hashed_id',
  ];


  // Accessor for hashed id (use in blades/routes)
  // public function getHashedIdAttribute()
  // {
  //   return Hashids::encode($this->permit_id);
  // }

  // approvals relation (event_approval_flow rows tied to this permit)
  public function approvals()
  {
    return $this->hasMany(EventApprovalFlow::class, 'permit_id', 'permit_id');
  }

  // organization relation
  public function organization()
  {
    return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
  }

  protected static function booted()
  {
    static::creating(function ($permit) {
      $permit->hashed_id = bin2hex(random_bytes(8)); // generates unique hash
    });
  }
}
