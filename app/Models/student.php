<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

class student extends  Authenticatable implements JWTSubject
{
    use Notifiable;

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

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function getTest($testCode)
    {
        $test = DB::table('tests')
            ->where('test_code', $testCode)
            ->first();

        return $test;
    }



    public function getQuestOfTest($testCode)
    {
        $result =
            quest_of_test::where('test_code', $testCode)
            ->join('questions', 'questions.question_id', '=', 'quest_of_test.question_id')
            ->inRandomOrder()
            ->get();

        return $result;
    }

    public function  addStudentQuest($student_id, $ID, $testCode, $question_id, $answer_a, $answer_b, $answer_c, $answer_d)
    {

        $status = DB::table('student_test_detail')->insert([
            'student_id' => $student_id,
            'ID' => $ID,
            'test_code' => $testCode,
            'question_id' => $question_id,
            'answer_a' => $answer_a,
            'answer_b' => $answer_b,
            'answer_c' => $answer_c,
            'answer_d' => $answer_d
        ]);

        return $status;
        // $studentTestDetail = new student_test_detail([
        //     'student_id' => $student_id,
        //     'ID'         => $ID,
        //     'test_code' => $testCode,
        //     'question_id' => $question_id,
        //     'answer_a' => $answer_a,
        //     'answer_b' => $answer_b,
        //     'answer_c' => $answer_c,
        //     'answer_d' => $answer_d,
        // ]);

        // $studentTestDetail->save();
    }

    public function updateStudentExam($ID, $testCode, $time)
    {
        $status = DB::table('students')
        ->where('student_id', $ID)
        ->update([
            'doing_exam' => $testCode,
            'time_remaining' => $time,
            'starting_time' => now()
        ]);

        return $status;
    }

    public function isStudent()
    {
        return $this->role === 'students';
    }
}
