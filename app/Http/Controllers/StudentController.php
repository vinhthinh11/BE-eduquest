<?php

namespace App\Http\Controllers;

use App\Models\practice;
use App\Models\practice_scores;
use App\Models\scores;
use App\Models\student;
use App\Models\student_practice_detail;
use App\Models\student_test_detail;
use App\Models\notifications;
use App\Models\student_notifications;
use App\Models\teacher_notifications;
use App\Models\tests;
use Carbon\Carbon;
use Carbon\CarbonTimeZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Ramsey\Uuid\Uuid;

class StudentController extends Controller
{
    public function getInfo($username)
    {
        $student = student::select('students.student_id', 'students.username', 'students.avatar', 'students.email', 'students.name', 'students.last_login', 'students.birthday', 'permissions.permission_detail', 'genders.gender_detail', 'genders.gender_id')
            ->join('permissions', 'students.permission', '=', 'permissions.permission')
            ->join('genders', 'students.gender_id', '=', 'genders.gender_id')
            ->where('students.username', '=', $username)
            ->first();
        if ($student) {
            //đẩy view ở đây nha!!
            //return view('student.info', ['student' => $student]);
            return response()->json(['student' => $student], 200);
        }
        return response()->json(['message' => 'Học sinh không tồn tại!'], 404);
    }
    public function getScore(Request $request){
        $student = $request->user('students');
        $scores = scores::where('student_id', $student->student_id)->get();
        return response()->json(['data' => $scores], 200);
    }
    public function getTest(Request $request){
    $user = $request->user('students');
    $grade_id = student::with("classes")->where("student_id", $user->student_id)->first()->classes->grade_id;
    $test = tests::where("grade_id", $grade_id)->where('status_id',"!=",3)->orderBy('timest','desc')->get();
    return response()->json(['data' => $test], 200);
    }
    public function getTestDetail(Request $request, $test_code)
    {
        $questions = [];
        $data  = tests::find($test_code);
        if (!$data) return response()->json(["message" => "Không tìm thấy đề thi!"], 400);
        foreach ($data->questions as $question) {
            $questions[] = $question;
        }
        $data['questions'] = $questions;

        return response()->json(["data" => $data]);
    }
    public function updateProfile(Request $request)
    {
        $me = $request->user('students');
        // $validator = Validator::make($request->all(), [
        //     'name' => 'sometimes|min:3|max:255',
        //     'gender_id' => 'sometimes|integer',
        //     'birthday' => 'sometimes|date',
        //     'password' => 'sometimes|min:6|max:20',
        //     'email' => 'sometimes|email|unique:admins,email',
        //     'avatar' => 'somtimes|mimes:jpeg,png,jpg,gif,svg|max:2048',
        // ]);

        // if ($validator->fails()) {
        //     return response()->json([
        //         'status' => false,
        //         'errors' => $validator->errors(),
        //     ], 422);
        // }

        if ($request->hasFile('avatar')) {
            if ($me->avatar != "avatar-default.jpg") {
                Storage::delete('public/' . str_replace('/storage/', '', $me->avatar));
            }
            $image = $request->file('avatar');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('images',  $imageName, 'public');
            $data['avatar'] = '/storage/' . $imagePath;
        }

        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        }

        $me->update($data);

        return response()->json([
            'status' => true,
            'message' => "Cập nhập tài khoản cá nhân thành công!"
        ]);
    }
    public function updateAvatarProfile(Request $request)
    {
        $user = $request->user('students');

        $validator = Validator::make($request->all(), [
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ], [
            'avatar.required' => 'Vui lòng chọn hình ảnh đại diện',
            'avatar.image' => 'Vui lòng chọn hình ảnh đại diện',
            'avatar.mimes' => 'Vui lòng chọn hình ảnh đúng định dạng (jpeg, png, jpg, gif, svg)',
            'avatar.max' => 'Kích thước hình ảnh không được vượt quá 2048KB',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        if ($request->hasFile('avatar')) {
            $image = $request->file('avatar');
            $path = $image->store('images/student');

            if ($user->avatar) {
                Storage::delete($user->avatar);
            }

            $user->avatar = $path;
            $user->save();

            return response()->json(['message' => 'Tải lên thành công', 'path' => $path], 200);
        }
        return response()->json(['message' => 'Không có tệp nào được tải lên'], 404);
    }
    public function updateDoingExam(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,student_id',
            'doing_exam' => 'required|boolean',
            'time_remaining' => 'required|integer|min:0|max:3600', //2700s=45p 3600s=1h
        ], [
            'student_id.required' => 'Trường student_id là bắt buộc.',
            'student_id.exists' => 'Học sinh không tồn tại.',
            'doing_exam.required' => 'Trường doing_exam là bắt buộc.',
            'doing_exam.boolean' => 'Trường doing_exam phải là boolean (true hoặc false).',
            'time_remaining.min' => 'Trường time_remaining không được nhỏ hơn 0 giây.',
            'time_remaining.max' => 'Trường time_remaining không được lớn hơn 3600 giây.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
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

    public function resetDoingExam(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,student_id',
            'doing_exam' => 'required|boolean',
            'time_remaining' => 'required|integer|min:0|max:3600', //2700s=45p 3600s=1h
        ], [
            'student_id.required' => 'Trường student_id là bắt buộc.',
            'student_id.exists' => 'Học sinh không tồn tại.',
            'doing_exam.required' => 'Trường doing_exam là bắt buộc.',
            'doing_exam.boolean' => 'Trường doing_exam phải là boolean (true hoặc false).',
            'time_remaining.min' => 'Trường time_remaining không được nhỏ hơn 0 giây.',
            'time_remaining.max' => 'Trường time_remaining không được lớn hơn 3600 giây.',
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

        $student->update([
            'doing_exam' => null,
            'time_remaining' => null,
            'starting_time' => null,
        ]);

        return response()->json(['status' => true, 'message' => 'Reset bài thi thành công!'], 200);
    }

    public function updateTiming(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,student_id',
            'min' => 'required|integer|min:0|max:59',
            'sec' => 'required|integer|min:0|max:59',
        ], [
            'student_id.required' => 'Trường student_id là bắt buộc.',
            'student_id.exists' => 'Học sinh không tồn tại.',
            'min.required' => 'Trường min là bắt buộc.',
            'min.min' => 'Trường min không được nhỏ hơn 0.',
            'min.max' => 'Trường min không được lớn hơn 59.',
            'sec.required' => 'Trường sec là bắt buộc.',
            'sec.min' => 'Trường sec không được nhỏ hơn 0.',
            'sec.max' => 'Trường sec không được lớn hơn 59.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
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

    public function getPractice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,student_id',
            'practice_code' => 'required|exists:practices,practice_code',
        ], [
            'student_id.required' => 'Trường student_id là bắt buộc.',
            'student_id.exists' => 'Học sinh không tồn tại.',
            'practice_code.required' => 'Trường practice_code là bắt buộc.',
            'practice_code.exists' => 'Bài tập không tồn tại.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $practiceCode = $request->practice_code;
        $student = Student::find($request->student_id);

        if (!$student) {
            return response()->json(['status' => false, 'message' => 'Học Sinh không tồn tại!'], 400);
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

    public function addTest(Request $request)
    {
        $result   = [];
        $student  = new student();
        $testCode = $request->test_code;
        $password = md5($request->password);
        $check  = $request->user('students');
        $id = $check->student_id;

        if ($password != $student->getTest($testCode)->password) {
            $result['status_value'] = "Sai mật khẩu";
            $result['status'] = 0;
        } else {
            $listQuest =  $student->getQuestOfTest($testCode);
            if ($listQuest !== null) {
                foreach ($listQuest as $quest) {
                    $array = array();
                    $array[0] = $quest->answer_a;
                    $array[1] = $quest->answer_b;
                    $array[2] = $quest->answer_c;
                    $array[3] = $quest->answer_d;
                    $ID = rand(1, time()) + rand(100000, 999999);
                    $time = $student->getTest($testCode)->time_to_do . ':00';
                    if (is_array($array) && count($array) >= 4) {
                        $student->addStudentQuest($id, $ID, $testCode, $quest->question_id, $array[0], $array[1], $array[2], $array[3]);
                    } else {
                        $result['status_value'] = "Không có đáp án";
                        $result['status'] = 0;
                    }
                    $student->updateStudentExam($id, $testCode, $time);
                }
                $result['status_value'] = "Thành công. Chuẩn bị chuyển trang!";
                $result['status'] = 1;
            } else {
                $result['status_value'] = "Không có câu hỏi cho bài kiểm tra này";
                $result['status'] = 0;
            }
        }


        return response()->json([
            'result' => $result,
        ]);
    }

    public function acceptTest(Request $request)
    {
        $student  = $request->user('students')->student_id;
        $doingExam = $request->user('students')->doing_exam;
        if (!$student) {
            return response()->json(['status' => false, 'message' => 'Học Sinh không tồn tại!'], 404);
        }
        $model = new student();
        $test = $model->getResultQuest($doingExam, $student);
        $testCode = $test->first()->test_code;
        $totalQuestions = $test->first()->total_questions;

        $correct = 0;
        $point = 10 / $totalQuestions;
        foreach ($test as $t) {
            if (trim($t->student_answer) == trim($t->correct_answer))
                $correct++;
        }
        $score = $correct * $point;
        $scoreDetail = $correct . '/' . $totalQuestions;
        $model->insertScore($student, $testCode, round($score, 2), $scoreDetail);
        $model->resetDoingExam($student);
        return response()->json(['status' => true, 'message' => 'Nộp bài Thành Công!'], 200);
    }
    /** Thực hiện nộp bài và tính điểm cho sinh viên */
    public function submitTest(Request $request)
    {
        $student  = $request->user('students');
        $test_code = $request->user('students')->doing_exam;
        // set doing_exam to null
        $student->doing_exam = null;
        $student->starting_time = null;
        $student->save();

        $total_question = tests::find($test_code)->total_questions;
        // check how many questions the student has answered is correct
        $correct  = student_test_detail::where('student_id', $student->student_id)
            ->where('test_code', $test_code)
            ->join('questions', 'student_test_detail.question_id', '=', 'questions.question_id')
            ->whereColumn('student_test_detail.student_answer', 'questions.correct_answer')
            ->count();
        // calculate the score
        $score = round($correct * 10 / $total_question,2);
        // save the score to the scores table
        scores::create([
            'student_id' => $student->student_id,
            'test_code' => $test_code,
            'score_number' => $score,
            'score_detail' => $correct . '/' . $total_question,
            'completion_time' => now(),
        ]);

        return response()->json(['status' => $student, 'correct' => $correct, 'score' => $score]);

    }


    public function showResult(Request $request)
    {

        $student  = $request->user('students');
        if ($student->doing_exam == '') {
            $testCode = $request->test_code;
            $score = scores::where('student_id', $student->student_id)
                ->where('test_code', $testCode)
                ->first();

            $result = DB::table('student_test_detail')
                ->join('questions', 'student_test_detail.question_id', '=', 'questions.question_id')
                ->join('tests', 'student_test_detail.test_code', '=', 'tests.test_code')
                ->where('student_test_detail.test_code', $testCode)
                ->where('student_test_detail.student_id', $student->student_id)
                ->orderBy('student_test_detail.ID')
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
        }
            // } else {
            //     $testCode = $student->doing_exam;

            //     $test = student_test_detail::join('questions', 'student_test_details.question_id', '=', 'questions.question_id')
            //         ->where('student_test_details.test_code', $testCode)
            //         ->where('student_test_details.student_id', $student->student_id)
            //         ->select('student_test_details.*', 'questions.question_content')
            //         ->orderBy('student_test_details.ID')
            //         ->get();

            //     $timeRemaining = explode(":", $student->time_remaining);
            //     $min = $timeRemaining[0];
            //     $sec = $timeRemaining[1];

            //     return response()->json([
            //         'status' => true,
            //         'data' => [
            //             'test' => $test,
            //             'time_remaining' => ['min' => $min, 'sec' => $sec],
            //         ],
            //         'message' => 'Show kết quả thi cho Học sinh thành công!',
            //     ], 200);
            // }
    }
    /** Thực hiện update cho current student set doing exam to test_code comming from request
     * @param $test_code
    */
    public function beginDoingTest(Request $request){
        $student = student::find( $request->user('students')->student_id );
        $testCode = $request->test_code;
        // check if if the if the time_to_to is valid
        $afterUpdate =$student->update([
            'doing_exam' => $testCode,
            'starting_time' => now(),
        ]);
        return response()->json([ 'student' => $afterUpdate]);

    }

    public function updateAnswer(Request $request)
    {
        // perform udpate student answer in student_test_detail table
        $question_id = $request->question_id;
        $student_id    = $request->user('students')->student_id;
        $testCode     = $request->user('students')->doing_exam;
        $student_answer     = $request->student_answer;
        // now our mission is to update the student answer
        // first check if the question_id exists in the student_test_detail table
        $student_test_detail = student_test_detail::where('question_id', $question_id)
            ->where('student_id', $student_id)
            ->where('test_code', $testCode)
            ->first();

        if ($student_test_detail) {
            // update the student answer
        $sql = "UPDATE student_test_detail
        SET student_answer = ?
        WHERE student_id = ? AND test_code = ? AND question_id = ?";
        DB::update($sql, [$student_answer, $student_id, $testCode, $question_id]);
            return response()->json(["message"=>"thuc hien update record"]);
        } else {
            // insert the student answer
            student_test_detail::create([
                'ID' => Uuid::uuid4()->toString(),
                'student_id' => $student_id,
                'test_code' => $testCode,
                'question_id' => $question_id,
                'student_answer' => $student_answer,
            ]);
            return response()->json(["message"=>"thuc hien create record"]);
        }
    }

    //danh sách thông báo
    public function getNotification(Request $request)
    {
        $student_id = $request->student_id;
        $student = Student::find($student_id);
        if (!$student) {
            return response()->json([
                'message' => 'Học sinh không tồn tại',
            ], 400);
        }
        $getList = notifications::whereExists(function ($query) use ($student_id) {
            $query->select(DB::raw(1))
                ->from('student_notifications')
                ->whereColumn('student_notifications.notification_id', 'notifications.notification_id')
                ->where('student_notifications.student_id', $student_id);
        })->get();
        if ($getList->isEmpty()) {
            return response()->json([
                'message' => 'Không tìm thấy dữ liệu',
            ], 400);
        }
        return response()->json([
            'message' => 'Thành công',
            'data' => $getList
        ]);
    }
}
