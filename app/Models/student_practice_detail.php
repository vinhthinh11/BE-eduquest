<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class student_practice_detail extends Model
{
    use HasFactory;

    protected $table = 'student_practice_detail';

    protected $fillable = [
        'ID',
        'student_id',
        'practice_code',
        'question_id',
        'answer_a',
        'answer_b',
        'answer_c',
        'answer_d',
        'student_answer',
        'timest'
    ];
}
