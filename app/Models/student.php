<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class student extends Model
{
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
}
