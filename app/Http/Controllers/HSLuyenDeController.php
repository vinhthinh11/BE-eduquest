<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\practice;
use App\Models\questions;
use App\Models\practice_scores;
use App\Models\student;
use App\Models\student_practice_detail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;
use Ramsey\Uuid\Uuid;

class HSLuyenDeController extends Controller
{
    public function list(Request $request){
        $student_id = $request->student_id;
        $getList = practice::where('student_id', $student_id)->get();
        if ($getList->isEmpty()) {
            return response()->json([
                'message' => 'No data found',
            ], 400);
        }
        return response()->json([
            'message' => 'success',
            'data' => $getList
        ]);
    }
    public function checkAddPractice(Request $request){
        $result = [];

        $grade_id = $request->grade_id;
        $subject_id = $request->subject_id;
        $level_id = $request->level_id;
        $time_to_do = 30;
        $total_question = 30;
        $student_id = $request->student_id;
        $student = $request->user('students');

        $total = questions::where('grade_id', $grade_id)
                        ->where('subject_id', $subject_id)
                        ->where('level_id', $level_id)
                        ->count();

        if (empty($grade_id) || empty($subject_id) || empty($level_id)) {
            $result['status_value'] = "Không được bỏ trống các trường nhập!";
            $result['status'] = 0;
        } else {
            if ($total >= $total_question) {
                $practice_code  = rand(10, 999999);

                $practice = new practice([
                    'practice_code' => $practice_code,
                    'grade_id' => $grade_id,
                    'subject_id' => $subject_id,
                    'level_id' => $level_id,
                    'time_to_do' => $time_to_do,
                    'total_question' => $total_question,
                    'student_id' => $student_id,
                ]);
                $practice->saveQuietly();
                $listQuest =  $student->getQuestOfPractice($practice_code);
                if ($listQuest !== null) {
                    foreach ($listQuest as $quest) {
                        $array = array();
                        $array[0] = $quest->answer_a;
                        $array[1] = $quest->answer_b;
                        $array[2] = $quest->answer_c;
                        $array[3] = $quest->answer_d;
                        $ID = rand(1, time()) + rand(100000, 999999);
                        $time = $student->getPractice($practice_code)->time_to_do . ':00';
                        if (is_array($array) && count($array) >= 4) {
                            $student->addStudentQuest(2, $ID, $practice_code, $quest->question_id, $array[0], $array[1], $array[2], $array[3]);
                        } else {
                            $result['status_value'] = "Không có đáp án";
                            $result['status'] = 0;
                        }
                        $student->updateStudentExam($practice_code, $time, 2);
                    }
                    $result['status_value'] = "Thành công. Chuẩn bị chuyển trang!";
                    $result['status'] = 1;
                } else {
                    $result['status_value'] = "Không có câu hỏi cho bài kiểm tra này";
                    $result['status'] = 0;
                }
            } else {
                $result['status_value'] = "Số lượng câu hỏi trong ngân hàng không đủ! Vui lòng chọn lại!";
                $result['status'] = 0;
                if ($total == 0) {
                    $result['status_value'] = "Không có câu hỏi nào trong ngân hàng câu hỏi!";
                }
            }
        }
        return response()->json([
            'result' => $result,
        ]);
    }
    public function acceptPractice(Request $request){
        $student_id = $request->student_id;
        $student = student::find('student_id', $student_id);
        $practiceResults = student_practice_detail::join('questions', 'student_practice_detail.question_id', '=', 'questions.question_id')
            ->join('practice', 'student_practice_detail.practice_code', '=', 'practice.practice_code')
            ->where('student_practice_detail.practice_code', $student->doing_practice)
            ->where('student_practice_detail.student_id', $student->student_id)
            ->orderBy('student_practice_detail.ID')
            ->get();

        $totalQuestions = $practiceResults->count();
        $correct = 0;

        foreach ($practiceResults as $result) {
            if (trim($result->student_answer) === trim($result->correct_answer)) {
                $correct++;
            }
        }

        $c = 10 / $totalQuestions;
        $score = $correct * $c;
        $scoreDetail = $correct . '/' . $totalQuestions;

        practice_scores::create([
            'student_id' => $student->id,
            'practice_code' => $practiceResults->first()->practice_code,
            'score_number' => round($score, 2),
            'score_detail' => $scoreDetail,
            'completion_time' => now(),
        ]);

        $student->update([
            'doing_practice' => null,
            'practice_time_remaining' => null,
            'practice_starting_time' => null,
        ]);

        return response()->json(['status' => true, 'message' => 'Nộp bài Thành Công!'], 200);
    }
}
