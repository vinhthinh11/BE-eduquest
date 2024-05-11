<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;

class quest_of_practice extends Model
{
    protected $table = 'quest_of_practice';
    protected $fillable = [
       'pratice_code',
       'question_id',
       'teacher_id',
    ];
    public $timestamps = false;

}
