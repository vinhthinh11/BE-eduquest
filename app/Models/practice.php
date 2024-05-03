<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;

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
    public function subject()
    {
        return $this->belongsTo(subjects::class, 'subject_id');
    }

    // public function scores()
    // {
    //     return $this->hasMany(practice_scores::class, 'practice_code');
    // }
}
