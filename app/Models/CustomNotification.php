<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomNotification extends Model
{
    protected $table = 'notifications'; // ← your custom table
    protected $primaryKey = 'notification_id';
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'message', 'notification_type', 'status'
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}
