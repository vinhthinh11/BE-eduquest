<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class student extends  Authenticatable implements JWTSubject
{
    use Notifiable;

    protected $table = 'students';

    protected $fillable = [
        'student_id',
        'username',
        'email',
        'password',
        'name',
        'permission',
        'class_id',
        'last_login',
        'gender_id',
        'avatar',
        'birthday',
        'doing_exam',
        'starting_time',
        'time_remaining',
        'doing_practice',
        'practice_time_remaining',
        'practice_starting_time',
    ];

    protected $primaryKey = 'student_id';
    public $timestamps = false;

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
