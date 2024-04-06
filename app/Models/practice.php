<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class practice extends Model
{
    protected $table = 'practice';

    protected $fillable = [
        'practice_code',
        'grade_id',
        'subject_id',
        'level_id',
        'time_to_do',
        'total_question',
        'student_id',
    ];
}
