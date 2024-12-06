<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = ['user_id', 'name', 'email'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function videoCalls()
    {
        return $this->hasMany(VideoCall::class, 'student_id');
    }

    public function tutors()
    {
        return $this->belongsToMany(Tutor::class, 'video_calls', 'student_id', 'tutor_id');
    }
}
