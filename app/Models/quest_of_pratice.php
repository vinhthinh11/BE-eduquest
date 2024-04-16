<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;

class quest_of_pratice extends Model
{
    protected $table = 'quest_of_pratice';
    protected $fillable = [
       'pratice_code',
       'question_id'
    ];
    
}
