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
    protected $casts = [
      'date_start' => 'date',
      'date_end' => 'date',
    ];
    // Accessor for hashed id (use in blades/routes)
    // public function getHashedIdAttribute()
    // {
    //   return Hashids::encode($this->permit_id);
    // }// In app/Models/Permit.php

    public function approvalFlow()
{
    return $this->hasMany(\App\Models\EventApprovalFlow::class, 'permit_id');
}
public function offCampusDocuments()
{
    return $this->hasMany(OffCampusRequirement::class, 'permit_id');
}

    // approvals relation (event_approval_flow rows tied to this permit)
    public function approvals()
  {
      return $this->hasMany(EventApprovalFlow::class, 'permit_id');
  }

    // organization relation
    public function organization()
  {
      return $this->belongsTo(Organization::class, 'organization_id');
  }
    // public function offCampusRequirements()
    // {
    //   // return $this->hasMany(OffCampusRequirement::class, 'permit_id', 'permit_id');
    // }
    protected static function booted()
    {
      static::creating(function ($permit) {
        $permit->hashed_id = bin2hex(random_bytes(8)); // generates unique hash
      });
    }
    public function isOffCampus()
    {
      return $this->type === 'Off-Campus';
    }
    public function getCurrentStatus()
    {
      $pending = $this->approvals()->where('status', 'pending')->first();

      if ($pending) {
        return 'Pending at ' . str_replace('_', ' ', $pending->approver_role);
      }

      $rejected = $this->approvals()->where('status', 'rejected')->first();
      if ($rejected) {
        return 'Rejected by ' . str_replace('_', ' ', $rejected->approver_role);
      }

      $allApproved = $this->approvals()->where('status', 'approved')->count() === $this->approvals()->count();
      if ($allApproved) {
        return 'Fully Approved';
      }

      return 'Processing';
    }
    public function getSubmittedRequirements()
    {
      return $this->offCampusRequirements()->pluck('requirement_type')->toArray();
    }
    public function hasAllRequiredDocuments()
    {
      if (!$this->isOffCampus()) {
        return true; // In-campus events don't need documents
      }

      return $this->offCampusRequirements()->count() > 0;
    }
    public function scopeForStudentOrganization($query, $userId)
    {
      return $query->whereHas('organization', function ($q) use ($userId) {
        $q->where('user_id', $userId);
      });
    }
    public function getApprovalStatusAttribute()
    {
      if ($this->approvals->count() === 0) {
        return 'pending'; // hasn't started approval yet
      }

      if ($this->approvals->contains('status', 'rejected')) {
        return 'rejected';
      }

      if ($this->approvals->contains('status', 'pending')) {
        return 'pending';
      }

      return 'approved';
    }
  }
