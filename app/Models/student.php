<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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

    public function getTest($testCode)
    {
        $test = DB::table('tests')
            ->where('test_code', $testCode)
            ->first();

        return $test;
    }

}
