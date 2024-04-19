<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

class questions extends  Model
{
    protected $table = 'questions';
    protected $fillable = [
        'grade_id',
        'unit' ,
        'level_id' ,
        'question_content',
        'answer_a',
        'answer_b',
        'answer_c',
        'answer_d',
        'correct_answer',
        'question_id',
        'subject_id' ,
        'teacher_id' ,
        'status_id' ,
        'suggest',
    ];
    public $timestamps = false;
    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }
    public function questOfTest()
    {
        return $this->belongsToMany(quest_of_test::class, 'test_code');
    }
    protected $primaryKey = 'question_id';
    function getQuestion()
    {
        $getAllQuestion = DB::select('select * from questions');
        return $getAllQuestion;
    }
    public function tests():BelongsToMany{
        return $this->belongsToMany(tests::class, 'quest_of_test', 'question_id', 'test_code');
    }

}
