<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venue extends Model
{
    use HasFactory;

    protected $table = 'venues';

    // Primary key is venue_id
    protected $primaryKey = 'venue_id';

    // Auto-incrementing key type
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'venue_name',
    ];
}
