<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class quest_of_test extends  Model
{
    protected $table = 'quest_of_test';
    protected $fillable = [
       'test_code',
       'question_id',
       'timest'
    ];
    public $timestamps = false;
    // protected $primaryKey = 'test_code';
    // protected $primaryKey2 = 'question_id';

}
