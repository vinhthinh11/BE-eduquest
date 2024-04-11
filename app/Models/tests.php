<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class tests extends  Model {
    protected $table = 'tests';
    protected $fillable = [
        'test_code',
        'test_name',
        'password',
        'subject_id',
        'grade_id',
        'level_id',
        'total_questions',
        'time_to_do',
        'note',
        'status_id',
        'timest'
    ];
    public $timestamps = false;
    protected $primaryKey = 'test_code';
}
