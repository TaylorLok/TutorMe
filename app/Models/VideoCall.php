<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VideoCall extends Model
{
    protected $fillable = [
        'tutor_id', 
        'student_id', 
        'agora_channel', 
        'scheduled_at', 
        'call_status'
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'call_status' => CallStatuses::class
    ];

    public function tutor()
    {
        return $this->belongsTo(User::class, 'tutor_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}