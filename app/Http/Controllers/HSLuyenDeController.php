<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\admin;
use App\Models\practice;
use App\Models\questions;
use App\Models\practice_scores;
use App\Models\scores;
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
    public function checkPractice(Request $request){
        $result = [];

        $grade_id = $request->grade_id;
        $subject_id = $request->subject_id;
        $level_id = $request->level_id;
        $time_to_do = 30;
        $total_question = 30;
        $student = $request->user('students');
        $student_id = $student->student_id;

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
                if ($practice) {
                    $result['status_value'] = "Thêm thành công!";
                    $result['status'] = 1;

                    $adminModel = new Admin();
                    $limit = $adminModel->calculateQuestionLevel($total, $level_id);
                    foreach ($limit as $level_id => $limitQuest) {
                        $listQuest = $adminModel->getListQuestByLevel($grade_id, $subject_id, $level_id, $limitQuest);
                        foreach ($listQuest as $quest) {
                            $adminModel->addQuestToTest($practice->practice_code, $quest->question_id);
                        }
                    }
                } else {
                    $result['status_value'] = "Thêm thất bại!";
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
    public function addPractice(Request $request)
    {
        $result   = [];
        $student  = new student();
        $practice_code = $request->practice_code;
        $check = Auth::guard('apiStudents')->user();
        $id = $check->student_id;
        $listQuest =  $student->getQuestOfTest($practice_code);
        if ($listQuest !== null) {
            foreach ($listQuest as $quest) {
                $array = array();
                $array[0] = $quest->answer_a;
                $array[1] = $quest->answer_b;
                $array[2] = $quest->answer_c;
                $array[3] = $quest->answer_d;
                $ID = rand(1, time()) + rand(100000, 999999);
                $time = $student->getTest($practice_code)->time_to_do . ':00';
                if (is_array($array) && count($array) >= 4) {
                    $student->addStudentQuest($id, $ID, $practice_code, $quest->question_id, $array[0], $array[1], $array[2], $array[3]);
                } else {
                    $result['status_value'] = "Không có đáp án";
                    $result['status'] = 0;
                }
                $student->updateStudentExam($practice_code, $time, $id);
            }
            $result['status_value'] = "Thành công. Chuẩn bị chuyển trang!";
            $result['status'] = 1;
        } else {
            $result['status_value'] = "Không có câu hỏi cho bài kiểm tra này";
            $result['status'] = 0;
        }
        
        return response()->json([
            'result' => $result,
        ]);
    }
    public function acceptPractice(Request $request){
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,student_id'
        ], [
            'student_id.*' => 'Học Sinh không tồn tại!',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        $student = Student::find($request->input('student_id'));
        if (!$student) {
            return response()->json(['status' => false, 'message' => 'Học Sinh không tồn tại!'], 404);
        }
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
    public function showPractice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,student_id',
            'practice_code' => 'required|exists:practice,practice_code',
        ], [
            'student_id.required' => 'Trường student_id là bắt buộc.',
            'student_id.exists' => 'Học sinh không tồn tại.',
            'practice_code.required' => 'Trường practice_code là bắt buộc.',
            'practice_code.exists' => 'Bài thi không tồn tại.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        $student = Student::find($request->input('student_id'));

        if (!$student) {
            return response()->json(['status' => false, 'message' => 'Học Sinh không tồn tại!'], 404);
        }

        if (!$student->doing_exam) {
            $score = scores::where('student_id', $student->student_id)
                ->where('practice_code', $request->input('practice_code'))
                ->first();

            $result = student_practice_detail::join('questions', 'student_practice_details.question_id', '=', 'questions.question_id')
                ->where('student_practice_details.practice_code', $request->input('practice_code'))
                ->where('student_practice_details.student_id', $student->student_id)
                ->select('student_practice_details.*', 'questions.question_content')
                ->orderBy('student_practice_details.ID')
                ->get();

            if ($score && $result->isNotEmpty()) {
                return response()->json([
                    'status' => true,
                    'data' => [
                        'score' => $score,
                        'result' => $result,
                    ],
                    'message' => 'Lấy điểm thi thành công!',
                ], 200);
            } else {
                return response()->json(['status' => false, 'message' => 'Không tìm thấy điểm hoặc kết quả!'], 404);
            }
        } else {
            $practiceCode = $student->doing_exam;

            $test = student_practice_detail::join('questions', 'student_practice_details.question_id', '=', 'questions.question_id')
                ->where('student_practice_details.practice_code', $practiceCode)
                ->where('student_practice_details.student_id', $student->student_id)
                ->select('student_practice_details.*', 'questions.question_content')
                ->orderBy('student_practice_details.ID')
                ->get();

            $timeRemaining = explode(":", $student->time_remaining);
            $min = $timeRemaining[0];
            $sec = $timeRemaining[1];

            return response()->json([
                'status' => true,
                'data' => [
                    'test' => $test,
                    'time_remaining' => ['min' => $min, 'sec' => $sec],
                ],
                'message' => 'Show kết quả thi cho Học sinh thành công!',
            ], 200);
        }
    }
}
