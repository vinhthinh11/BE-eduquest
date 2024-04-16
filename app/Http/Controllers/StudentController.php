<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\Student\DeleteStudentRequest;
use App\Http\Requests\Student\GetPracticeRequest;
use App\Http\Requests\Student\ShowResultRequest;
use App\Http\Requests\Student\UpdateDoingExamRequest;
use App\Http\Requests\Student\UpdateTimingRequest;
use App\Models\practice;
use App\Models\practice_scores;
use App\Models\scores;
use App\Models\student;
use App\Models\student_practice_detail;
use App\Models\student_test_detail;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;

class StudentController extends Controller
{
    public function updateDoingExam(UpdateDoingExamRequest $request)
    {
        $data = $request->only(['student_id', 'doing_exam', 'time_remaining']);

        if (!isset($data['student_id']) || !isset($data['doing_exam']) || !isset($data['time_remaining'])) {
            return response()->json(['status' => false, 'message' => 'Không có đủ dữ liệu được truyền!'], 400);
        }

        $student = student::find($data['student_id']);

        if (!$student) {
            return response()->json(['status' => false, 'message' => 'Học Sinh không tồn tại!'], 404);
        }

        $student->update([
            'doing_exam' => $data['doing_exam'],
            'time_remaining' => $data['time_remaining'],
            'starting_time' => now(),
        ]);

        return response()->json(['status' => true, 'message' => 'Cập Nhập thao tác Thi thành công!'], 200);
    }

    public function resetDoingExam(UpdateDoingExamRequest $request)
    {
        $student = Student::find($request->input('student_id'));

        if (!$student) {
            return response()->json(['status' => false, 'message' => 'Học Sinh không tồn tại!'], 404);
        }

        $student->update([
            'doing_exam' => null,
            'time_remaining' => null,
            'starting_time' => null,
        ]);

        return response()->json(['status' => true, 'message' => 'Reset bài thi thành công!'], 200);
    }

    public function updateTiming(UpdateTimingRequest $request)
    {
        $data = $request->only(['student_id', 'min', 'sec']);

        $student = Student::find($data['student_id']);

        if (!$student) {
            return response()->json(['status' => false, 'message' => 'Học Sinh không tồn tại!'], 404);
        }

        $time = $data['min'] . ':' . $data['sec'];
        $student->time_remaining = $time;
        $student->save();

        return response()->json(['status' => true, 'message' => 'Cập Nhập thời gian thi Thành công!'], 200);
    }

    public function getPractice(GetPracticeRequest $request)
    {
        $practiceCode = $request->input('practice_code', '493205');
        $student = Student::find($request->input('student_id'));

        if (!$student) {
            return response()->json(['status' => false, 'message' => 'Học Sinh không tồn tại!'], 404);
        }

        $listQuest = student_practice_detail::join('questions', 'student_practice_detail.question_id', '=', 'questions.question_id')
            ->where('practice_code', $practiceCode)
            ->get();

        foreach ($listQuest as $quest) {
            $ID = Uuid::uuid4()->toString();
            $time = practice::where('practice_code', $practiceCode)->first()->time_to_do . ':00';

            $student->questions()->attach($quest->question_id, [
                'ID' => $ID,
                'practice_code' => $practiceCode,
                'answer_a' => $quest->answer_a,
                'answer_b' => $quest->answer_b,
                'answer_c' => $quest->answer_c,
                'answer_d' => $quest->answer_d,
            ]);
        }

        $student->update([
            'doing_exam' => $practiceCode,
            'time_remaining' => $time,
            'starting_time' => now(),
        ]);

        return response()->json(['status' => true, 'message' => 'Thành công. Chuẩn bị chuyển trang!'], 200);
    }

    public function acceptTest(DeleteStudentRequest $request) //dùng request này vì chỉ cần lấy ID Học sinh
    {
        $student = Student::find($request->input('student_id'));

        if (!$student) {
            return response()->json(['status' => false, 'message' => 'Học Sinh không tồn tại!'], 404);
        }

        $testResults = student_test_detail::join('questions', 'student_test_details.question_id', '=', 'questions.question_id')
            ->join('tests', 'student_test_details.test_code', '=', 'tests.test_code')
            ->where('student_test_details.test_code', $student->doing_exam)
            ->where('student_test_details.student_id', $student->student_id)
            ->orderBy('student_test_details.ID')
            ->get();

        $totalQuestions = $testResults->count();
        $correct = $testResults->where('student_answer', trim($testResults->first()->correct_answer))->count();

        $c = 10 / $totalQuestions;
        $score = $correct * $c;
        $scoreDetail = $correct . '/' . $totalQuestions;

        practice_scores::create([
            'student_id' => $student->id,
            'practice_code' => $testResults->first()->test_code,
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

    public function acceptPractice(DeleteStudentRequest $request)
    {
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

    public function showResult(ShowResultRequest $request)
    {
        $student = Student::find($request->input('student_id'));

        if (!$student) {
            return response()->json(['status' => false, 'message' => 'Học Sinh không tồn tại!'], 404);
        }

        if (!$student->doing_exam) {
            $score = scores::where('student_id', $student->student_id)
                ->where('test_code', $request->input('test_code'))
                ->first();

            $result = student_test_detail::join('questions', 'student_test_details.question_id', '=', 'questions.question_id')
                ->where('student_test_details.test_code', $request->input('test_code'))
                ->where('student_test_details.student_id', $student->student_id)
                ->select('student_test_details.*', 'questions.question_content')
                ->orderBy('student_test_details.ID')
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
            $testCode = $student->doing_exam;

            $test = student_test_detail::join('questions', 'student_test_details.question_id', '=', 'questions.question_id')
                ->where('student_test_details.test_code', $testCode)
                ->where('student_test_details.student_id', $student->student_id)
                ->select('student_test_details.*', 'questions.question_content')
                ->orderBy('student_test_details.ID')
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
