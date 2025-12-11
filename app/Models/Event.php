<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
  protected $table = 'events'; // ✅ IMPORTANT — table name must match what exists
  protected $primaryKey = 'event_id';
  public $timestamps = false;

  protected $fillable = [
   'organization_id',
    'event_title',
    'event_date',
    'venue_id',
    'proposal_status',
    'current_stage',
    'event_report_submitted',
    'event_permit_submitted',
  ];

  // ✅ Link to the approval flow
  public function approvals()
  {
    return $this->hasMany(EventApprovalFlow::class, 'permit_id', 'permit_id');
  }
  public function organization()
{
    return $this->belongsTo(Organization::class, 'organization_id');
}

public function venue()
{
    return $this->belongsTo(Venue::class, 'venue_id');
}
}
