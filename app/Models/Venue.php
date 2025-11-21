<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venue extends Model
{
    use HasFactory;

    protected $table = 'venues';
    protected $primaryKey = 'venue_id';

    protected $fillable = [
        'venue_name',
    ];

    /**
     * Get all events using this venue
     */
    public function events()
    {
        return $this->hasMany(Event::class, 'venue_id', 'venue_id');
    }
}
