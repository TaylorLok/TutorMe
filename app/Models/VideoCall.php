<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\CallStatuses;

class VideoCall extends Model
{
    protected $fillable = [
        'caller_id', 
        'receiver_id', 
        'agora_channel', 
        'scheduled_at', 
        'call_status'
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'call_status' => CallStatuses::class
    ];

    public function caller()
    {
        return $this->belongsTo(User::class, 'caller_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}
