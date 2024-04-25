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
    public function updateProfile(Request $request)
    {
        $data['id'] = $request->id;
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3|max:255',
            'gender_id' => 'required',
            'birthday' => 'nullable|date',
            'password' => 'required|min:6|max:20',
            'email' => 'nullable|email|unique:students,email,'.$data['id'].',student_id',
        ], [
            'name.required' => 'Vui lòng nhập tên!',
            'name.min' => 'Tên cần ít nhất 3 ký tự!',
            'name.max' => 'Tên dài nhất 255 ký tự!',
            'gender_id.required' => 'Vui lòng chon giới tính!',
            'birthday.date' => 'Ngày sinh chưa đúng định dạng!',
            'password.required' => 'Vui lòng nhập mật khẩu!',
            'password.min' => 'Vui nhap it nhat 6 ky tu!',
            'email.email' => 'Vui long nhap email hop le!',
            'email.unique' => 'Email da ton tai!',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        $me = student::find($request->id);
        $me->update([
                    'name' => $request['name'],
                    'email' => $request['email'],
                    'gender_id' => $request['gender_id'],
                    'birthday' => $request['birthday'],
                    'password' => bcrypt($request['password']),
                    'last_login' => Carbon::now(CarbonTimeZone::createFromHourOffset(7 * 60))->timezone('Asia/Ho_Chi_Minh'),
                ]);
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

    public function acceptTest(Request $request)
    {
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

    public function acceptPractice(Request $request)
    {
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

    //fix bug phần này
    public function showResult(Request $request)
    {
        $student = Student::find(11);
        if (!$student) {
            return response()->json(['status' => false, 'message' => 'Học Sinh không tồn tại!'], 400);
        }

        if (!$student->doing_exam) {
            $score = scores::where('student_id', $request->student_id)
                ->where('test_code', $request->test_code)
                ->first();

            $result = student_test_detail::join('questions', 'student_test_detail.question_id', '=', 'questions.question_id')
                ->where('student_test_detail.test_code', $request->test_code)
                ->where('student_test_detail.student_id', $student->student_id)
                ->select('student_test_detail.*', 'questions.question_content')
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
        } else {
            $testCode = $student->doing_exam;

            $test = student_test_detail::join('questions', 'student_test_detail.question_id', '=', 'questions.question_id')
                ->where('student_test_detail.test_code', $testCode)
                ->where('student_test_detail.student_id', $student->student_id)
                ->select('student_test_detail.*', 'questions.question_content')
                ->orderBy('student_test_detail.ID')
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
   public function updateAnswer(Request $request)
   {
       $validator = Validator::make($request->all(), [
           'student_id' => 'numeric',
           'test_code' => 'required|string',
           'id' => 'required|numeric',
           'answer' => 'required|string',
           'min' => 'required|numeric|min:0|max:60',
           'sec' => 'required|numeric|min:0|max:60',
       ], [
           'student_id.numeric' => 'Mã sinh viên phải là một số.',
           'test_code.required' => 'Vui lòng nhập mã bài thi.',
           'test_code.string' => 'Mã bài thi phải là một chuỗi.',
           'id.required' => 'Vui lòng nhập mã câu hỏi.',
           'id.numeric' => 'Mã câu hỏi phải là một số.',
           'answer.required' => 'Vui lòng nhập đáp án.',
           'min.required' => 'Vui lòng nhập phút.',
           'min.numeric' => 'Phút phải là một số.',
           'min.min' => 'Phút phải là một số nguyên dương.',
           'min.max' => 'Phút phải là một số nhỏ hơn hoặc bằng 60.',
           'sec.required' => 'Vui lòng nhập giây.',
           'sec.numeric' => 'Giây phải là một số.',
           'sec.min' => 'Giây phải là một số nguyên dương.',
           'sec.max' => 'Giây phải là một số nhỏ hơn hoặc bằng 60.',
       ]);

       if ($validator->fails()) {
           return response()->json(['errors' => $validator->errors()], 422);
       }

       $data = $request->only(['student_id', 'test_code', 'id', 'answer', 'min', 'sec']);

       DB::table('student_test_detail')
           ->where('student_id', $data['student_id'])
           ->where('test_code', $data['test_code'])
           ->where('question_id', $data['id'])
           ->update(['student_answer' => $data['answer']]);

       $total_seconds = ($data['min'] * 60) + $data['sec'];

       DB::table('students')
           ->where('student_id', $data['student_id'])
           ->update(['time_remaining' => $total_seconds]);

       return response()->json([
           'status' => true,
           'message' => 'Cập nhật đáp án cho Học sinh thành công!',
           'data' => ['student_answer' => $data['answer'], 'time_remaining' => $total_seconds]
       ]);
   }

   //danh sách thông báo
   public function getNotification(Request $request){
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
