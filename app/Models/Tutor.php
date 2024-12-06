<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tutor extends Model
{

    protected $fillable = ['user_id', 'name', 'email', 'subject'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function videoCalls()
    {
        return $this->hasMany(VideoCall::class, 'tutor_id');
    }
 
    public function students()
    {
        return $this->belongsToMany(Student::class, 'video_calls', 'tutor_id', 'student_id');
    }
}
