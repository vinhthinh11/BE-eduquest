<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class scores extends Model
{
    protected $table = 'scores';

    protected $fillable = [
        'student_id',
        'test_code',
        'score_number',
        'score_detail',
        'completion_time'
    ];
    public $timestamps = false;
}
