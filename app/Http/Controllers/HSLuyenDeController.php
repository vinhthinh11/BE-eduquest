<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\practice;
use App\Models\questions;
use App\Models\quest_of_pratice;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

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
    public function luyenDe(Request $request){
        $result = [];
    
        $grade_id = $request->grade_id;
        $subject_id = $request->subject_id;
        $level_id = $request->level_id;
        $time_to_do = 30;
        $total_question = 30; 
        $student_id = $request->student_id;
    
        $total = questions::where('grade_id', $grade_id)
                        ->where('subject_id', $subject_id)
                        ->where('level_id', $level_id)
                        ->count();
    
        if (empty($grade_id) || empty($subject_id) || empty($level_id)) {
            $result['status_value'] = "Không được bỏ trống các trường nhập!";
            $result['status'] = 0;
        } else {
            if ($total > $total_question) {
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
                    // Lấy danh sách câu hỏi đủ điều kiện
                    $questions = questions::where('grade_id', $grade_id)
                        ->where('subject_id', $subject_id)
                        ->where('level_id', $level_id)
                        ->inRandomOrder()
                        ->limit($total_question)
                        ->pluck('id');
    
                    // Lưu practice_code và question_id vào bảng quest_of_pratice
                    foreach ($questions as $question_id) {
                        DB::table('quest_of_pratice')->insert([
                            'practice_code' => $practice_code,
                            'question_id' => $question_id,
                        ]);
                    }
    
                    $result['status_value'] = "Thành công. Đang chuyển hướng trang!";
                    $result['status'] = 1;
                } else {
                    $result['status_value'] = "Thất bại. Vui lòng chọn lại!";
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
    public function nopBai(Request $request){
        $student_id = $request->student_id;
        $practice_id = $request->practice_id;
        $score_number = $request->score_number;
        $score_detail = $request->score_detail;
        $completion_time = $request->completion_time;

        $score_practice = new ScorePractice([
            'student_id' => $student_id,
            'practice_id' => $practice_id,
            'score_number' => $score_number,
            'score_detail' => $score_detail,
            'completion_time' => $completion_time,
        ]);

        $score_practice->save();

        if ($score_practice) {
            return response()->json([
                'message' => 'Nộp bài thi thành công',
            ], 200);
        } else {
            return response()->json([
                'message' => 'Nộp bài thi thất bại',
            ], 400);
        }
    }
}
