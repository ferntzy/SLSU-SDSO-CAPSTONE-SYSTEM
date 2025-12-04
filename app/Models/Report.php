<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $primaryKey = 'document_id';
    protected $fillable = [
        'event_id', 'document_type', 'title', 'description', 'permit_id',
        'document_url', 'original_filename', 'mime_type', 'file_size'
    ];

    public function permit()
    {
        return $this->belongsTo(Permit::class, 'event_id');
    }
}
