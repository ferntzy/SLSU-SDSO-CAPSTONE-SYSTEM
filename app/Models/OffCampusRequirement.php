<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OffCampusRequirement extends Model
{
    protected $table = 'off_campus_requirements';

    protected $fillable = [
        'permit_id',
        'requirement_type',
        'file_path',
        'original_filename',
        'file_size',
        'mime_type'
    ];

    public function permit()
    {
        return $this->belongsTo(Permit::class);
    }
}
