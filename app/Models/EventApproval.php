<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventApproval extends Model
{

  use HasFactory;

  protected $table = 'event_approvals';
  protected $primaryKey = 'approval_id';
  protected $fillable = [
    'event_id',
    'approver_role',
    'approver_id',
    'status',
    'comments',
    'approved_at',
  ];

  public function event()
  {
    return $this->belongsTo(Event::class, 'event_id');
  }

  public function approver()
  {
    return $this->belongsTo(User::class, 'approver_id');
  }
}
