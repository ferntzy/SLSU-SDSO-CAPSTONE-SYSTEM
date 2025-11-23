<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OffCampusRequirement extends Model
{
    use HasFactory;

    protected $primaryKey = 'requirement_id'; // important if not using 'id'

    protected $fillable = [
        'permit_id',
        'requirement_type',
        'file_path',
    ];

    public function permit()
    {
        // belongsTo(parent model, foreign key, owner key)
        return $this->belongsTo(Permit::class, 'permit_id', 'permit_id');
    }

    public function getRequirementLabel()
    {
        return [
            'curriculum_requirement' => 'Curriculum Requirement',
            'destination_handbook' => 'Destination Handbook or Manual',
            'notarized_parents_consent' => 'Notarized Parents Consent',
            'medical_certificate' => 'Medical Certificate',
            'personnel_in_charge' => 'Personnel-in-Charge',
            'first_aid_kit' => 'First Aid Kit',
            'fee_fund' => 'Fee/Fund',
            'insurance' => 'Insurance',
            'vehicle' => 'Vehicle',
            'lgu_ngo' => 'LGU/NGO',
            'orientation_briefing' => 'Orientation/Briefing',
            'learning_journals' => 'Learning Journals',
            'emergency_preparedness_plan' => 'Emergency Preparedness Plan',
        ][$this->requirement_type] ?? $this->requirement_type;
    }
}
