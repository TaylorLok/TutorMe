<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserTypes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;


class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'user_type'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'user_type' => UserTypes::class,
        ];
    }

    public function tutorProfile()
    {
        return $this->hasOne(Tutor::class);
    }

    public function studentProfile()
    {
        return $this->hasOne(Student::class);
    }

    public function teachingSessions()
    {
        return $this->hasMany(VideoCall::class, 'tutor_id');
    }

    public function learningSessions()
    {
        return $this->hasMany(VideoCall::class, 'student_id');
    }

    public function tutorVideoCalls()
    {
        return $this->hasMany(VideoCall::class, 'tutor_id');
    }

    public function studentVideoCalls()
    {
        return $this->hasMany(VideoCall::class, 'student_id');
    }
}
