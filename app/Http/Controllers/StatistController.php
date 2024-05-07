<?php

namespace App\Http\Controllers;

use App\Models\classes;
use App\Models\notifications;
use App\Models\practice;
use App\Models\practice_scores;
use App\Models\quest_of_practice;
use App\Models\quest_of_test;
use App\Models\questions;
use App\Models\scores;
use App\Models\student;
use App\Models\student_notifications;
use App\Models\student_practice_detail;
use App\Models\subject_head;
use App\Models\subjects;
use App\Models\teacher;
use App\Models\teacher_notifications;
use App\Models\tests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use SebastianBergmann\CodeCoverage\Report\Xml\Tests as XmlTests;

class StatistController extends Controller
{
    //Thống kê của ADMIN
    public function statist(Request $request)
    {
        $query = subjects::select('subjects.subject_detail', 'subjects.subject_id')
            ->leftJoin('tests', 'subjects.subject_id', '=', 'tests.subject_id')
            ->leftJoin('scores', 'tests.test_code', '=', 'scores.test_code')
            ->groupBy('subjects.subject_detail', 'subjects.subject_id');

        if ($request->grade_id) {
            $query->selectRaw('SUM(IF(scores.test_code IS NOT NULL AND tests.grade_id = ?, 1, 0)) AS tested_time', [$request->grade_id]);
        } else {
            $query->selectRaw('SUM(IF(scores.test_code IS NOT NULL, 1, 0)) AS tested_time');
        }

        $statistics = $query->get();

        return response()->json([
            'message' => 'Thống kê thành công!',
            'data' => $statistics
        ]);
    }
    public function statistScores(Request $request)
    {
        $query = scores::selectRaw('SUM(IF(scores.score_number < 5, 1, 0)) AS bad, SUM(IF(scores.score_number >= 5 AND scores.score_number < 6.5, 1, 0)) AS complete, SUM(IF(scores.score_number >= 6.5 AND scores.score_number < 8, 1, 0)) AS good, SUM(IF(scores.score_number >= 8, 1, 0)) AS excellent')
            ->leftJoin('tests', 'scores.test_code', '=', 'tests.test_code');

        if ($request->grade_id) {
            $query->where('tests.grade_id', $request->grade_id);
        }

        $statistics = $query->get();

        return response()->json([
            'message' => 'Thống kê điểm số thành công!',
            'data' => $statistics
        ]);
    }

    //Thống kê của học sinh
    public function statistStudent(Request $request)
    {
        $statistics = subjects::select('subjects.subject_detail', 'subjects.subject_id', DB::raw('SUM(IF(practice_scores.practice_code IS NOT NULL, 1, 0)) AS tested_time'))
            ->leftJoin('practice', 'subjects.subject_id', '=', 'practice.subject_id')
            ->leftJoin('practice_scores', 'practice.practice_code', '=', 'practice_scores.practice_code')
            ->where('practice.student_id', $request->user()->id)
            ->groupBy('subjects.subject_detail', 'subjects.subject_id')
            ->get();

        return response()->json([
            'message' => 'Thống kê thành công!',
            'data' => $statistics
        ]);
    }
    public function subjectScore(Request $request)
    {
        $statistics = practice_scores::select('practice_scores.score_number as score', 'practice_scores.completion_time as day')
            ->leftJoin('practice', 'practice_scores.practice_code', '=', 'practice.practice_code')
            ->leftJoin('subjects', 'practice.subject_id', '=', 'subjects.subject_id')
            ->where('practice.student_id', auth()->id())
            ->where('practice.subject_id', $request->subject_id)
            ->orderBy('practice_scores.completion_time')
            ->limit(10)
            ->get();

        return response()->json([
            'message' => 'Thống kê điểm môn học thành công!',
            'data' => $statistics
        ]);
    }

    public function allAdminPage()
    {
        $tableCounts = [
            'teacher' => teacher::count(),
            'student' => student::count(),
            'head' => subject_head::count(),
            'question' => questions::count(),
            'test' => tests::count(),
            'score' => scores::count(),
            'practice' => practice::count(),
            'practice_scores' => practice_scores::count(),
        ];

        return response()->json([
            'message' => 'Thống kê trả về dữ liệu số lượng bản ghi!',
            'data'=>$tableCounts
        ], 200);
    }
    public function allStudentPage()
    {
        $openTestCount = tests::where('status_id', '2')->count();
        $tableCounts = [
            'practice' => practice::count(),
            'test' => $openTestCount,
            'chat' => student_notifications::count(),
            'notification' => notifications::count(),
        ];

        return response()->json([
            'message' => 'Thống kê trả về dữ liệu số lượng bản ghi!',
            'data'=>   $tableCounts
        ], 200);
    }
    //mấy cái ở dưới đây querry lấy dữ liệu mà thiếu nhiều column quá :))))
    //m xem làm được không thì fix thử t mỏi mắt quá rồi -.-
    // với lại xem cái login head_subject nha t vào không được!!!!
    public function allTeacherPage($teacher_id)
    {
        // Tìm id của các lớp mà giáo viên đó là chủ nhiệm
        $class_ids = classes::where('teacher_id', $teacher_id)->pluck('class_id')->toArray();

        // Lấy danh sách học sinh thuộc các lớp mà giáo viên đó là chủ nhiệm
        $students = student::whereIn('class_id', $class_ids)->pluck('student_id')->toArray();

        // Đếm số lượng câu hỏi mà giáo viên đó tạo
        $question_test = quest_of_test::whereIn('teacher_id', [$teacher_id])->count();
        $question_practice = quest_of_practice::whereIn('teacher_id', [$teacher_id])->count();
        $question_count = questions::where('teacher_id', $teacher_id)->count();

        $tableCounts = [
            'ngân hàng câu hỏi'=> questions::count(),
            'câu hỏi của giáo viên' => $question_count,
            'câu hỏi test của giáo viên' => $question_test,
            'câu hỏi luyện thi của giáo viên' => $question_practice,
            'test' => tests::count(),
            'practice' => practice::count(),
            'câu hỏi test' => quest_of_test::count(),
            'thông báo của admin' => teacher_notifications::where('teacher_id', $teacher_id)->count(),
            'thông báo cho học sinh' => student_notifications::whereIn('class_id', $class_ids)->count(),
            'điểm của học sinh trong lớp' => scores::whereIn('student_id', $students)->count(),
        ];

        return response()->json([
            'message' => 'Thống kê trả về dữ liệu số lượng bản ghi!',
            'data' => $tableCounts
        ], 200);
    }

    public function allHeadPage($subject_head_id)
    {
        // Lấy danh sách các môn học mà trưởng bộ môn làm trưởng
        $subjects = subjects::where('subject_id', $subject_head_id)->pluck('subject_id')->toArray();

        // Đếm số lượng câu hỏi trong các môn học đó
        $question_count = Questions::whereIn('subject_id', $subjects)->count();

        // Đếm số lượng đề thi trong các môn học đó
        $test_count = Tests::whereIn('subject_id', $subjects)->count();

        $tableCounts = [
            'subject' => $subjects,
            'student' => Student::count(),
            'test' => $test_count,
            'score' => Scores::count(),
            'practice' => Practice::count(),
            'practice_scores' => Practice_Scores::count(),
            'câu hỏi trong môn học' => $question_count,
        ];

        return response()->json([
            'message' => 'Thống kê trả về dữ liệu số lần bản ghi!',
            'data' => $tableCounts
        ], 200);
    }

}
