<?php

namespace App\Http\Controllers;

use App\Models\admin;
use App\Models\questions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Http\Controllers\Controller;
use App\Models\grade;
use App\Models\level;
use App\Models\status;
use App\Models\subjects;
use Tymon\JWTAuth\Facades\JWTAuth;


class Admincontroller extends Controller
{
    public function getAdmin()
    {

        $admin = new admin();
        $getAllAdmin = $admin->getAdmin();

        return response()->json([
            'getAllAdmin' => $getAllAdmin,
        ]);
        // } else {
        // }
    }

    public function indexLogin()
    {
        return view('loginTest');
    }

    // public function __construct()
    // {
    //     $this->middleware('auth:api', ['except' => ['submitLogin']]);
    // }

    public function submitLogin(Request $request)
    {
        $result = [];

        if ($request->has('email') && $request->has('password')) {
            $email = $request->input('email');
            $password = $request->input('password');

            $token  = Auth::guard('api')->attempt([
                'email'    => $email,
                'password'    => $password,
            ]);
            session()->put('permission', 'admin');
            // dd($token);
            if ($token) {
                $result['status_value'] = 'Đăng nhập thành công đang chuyển hướng...';
            } else {
                $result['status_value'] = 'Đăng nhập thất bại!';
            }
        }

        return response()->json([
            'result' =>  $result,
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 6000
        ]);
    }


    public function logout(Request $request)
    {
        Auth::guard('api')->logout();
        return redirect('api/admin/login');
    }


    public function check_add_admin_via_file(Request $request)
    {
        $result = [];

        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->path();

            $reader = IOFactory::createReader('Xlsx');
            $spreadsheet = $reader->load($filePath);
            $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

            $count = 0;
            $errList = [];

            foreach ($sheetData as $key => $row) {
                if ($key < 4) {
                    continue;
                }

                if (empty($row['A'])) {
                    continue;
                }

                $name = $row['B'];
                $username = $row['C'];
                $email = $row['D'];
                $password = bcrypt($row['E']);
                $birthday = $row['F'];
                $gender = ($row['G'] == 'Nam') ? 2 : (($row['G'] == 'Nữ') ? 3 : 1);
                $admin = new Admin([
                    'name' => $name,
                    'username' => $username,
                    'email' => $email,
                    'password' => $password,
                    'birthday' => $birthday,
                    'gender_id' => $gender,
                    'last_login' => now(),
                ]);

                if ($admin->saveQuietly()) {
                    $count++;
                } else {
                    $errList[] = $row['A'];
                }
            }

            unlink($filePath);

            if (empty($errList)) {
                $result['status_value'] = "Thêm thành công " . $count . " tài khoản!";
                $result['status'] = 1;
            } else {
                $result['status_value'] = "Lỗi! Không thể thêm tài khoản có STT: " . implode(', ', $errList) . ', vui lòng xem lại.';
                $result['status'] = 0;
            }
        } else {
            $result['status_value'] = "Không tìm thấy tệp được tải lên!";
            $result['status'] = 0;
        }

        return response()->json($result);
        // return response()->json([
        //     'result' => $result,
        // ]);
    }

    public function indexAdmin()
    {
        return view('admin.CRUD');
    }
    public function createAdmin(Request $request)
    {
        $result = [];

        $name = $request->input('name');
        $username = $request->input('username');
        $password = bcrypt($request->input('password'));
        $email = $request->input('email');
        $birthday = $request->input('birthday');
        $gender = $request->input('gender');

        $admin = new Admin([
            'name' => $name,
            'username' => $username,
            'password' => $password,
            'email' => $email,
            'birthday' => $birthday,
            'gender_id' => $gender,
            'last_login' => now(),

        ]);

        // Lưu admin mới vào cơ sở dữ liệu
        if ($admin->save()) {
            $result = $admin->toArray();
            $result['status_value'] = "Thêm thành công!";
            $result['status'] = 1;
        } else {
            $result['status_value'] = "Lỗi! Tài khoản đã tồn tại!";
            $result['status'] = 0;
        }
        // return response()->json($result);
        return response()->json([
            'result' => $result,
        ]);
    }

    public function deleteAdmin(Request $request)
    {
        $admin = Admin::find($request->admin_id);
        // dd($admin);
        if ($admin) {
            $admin->delete();
            return response()->json([
                'status'    => true,
                'message'   => 'Xoá admin thành công!',
            ]);
        } else {
            return response()->json([
                'status'    => false,
                'message'   => 'Không tìm thấy admin!',
            ], 404);
        }
    }


    public function updateAdmin(Request $request)
    {
        $admin = Admin::where('id', $request->id)->first();

        $data = $admin->all();
        if (isset($admin)) {
            $request->update($data);
            return response()->json([
                'status'    => true,
                'message'   => 'Cập nhật khách hàng thành công!',
            ]);
        } else {
            return response()->json([
                'status'    => false,
                'message'   => 'Cập nhật khách hàng không thành công!',
            ]);
        }
    }

    public function getQuestion()
    {
        $admin = new questions();
        $getQuestions = $admin->getQuestion();

        return response()->json([
            'getQuestions' => $getQuestions,
        ]);
    }

    public function getLevels()
    {
        $level = level::get();

        return response()->json([
            'level' => $level,
        ]);
    }

    public function getGrades()
    {
        $grade = grade::get();

        return response()->json([
            'grade' => $grade,
        ]);
    }

    public function getStatus()
    {
        $status = status::get();

        return response()->json([
            'status' => $status,
        ]);
    }

    public function getSubjects()
    {
        $subjects = subjects::get();

        return response()->json([
            'subjects' => $subjects,
        ]);
    }


    public function checkAddQuestionViaFile(Request $request)
    {
        $result = [];

        $subjectId = $request->subject_id;
        // $subjectId = 10;
        $inputFileType = 'Xlsx';
        $count = 0;
        $errList = [];

        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->path();

            $reader = IOFactory::createReader($inputFileType);
            $spreadsheet = $reader->load($filePath);
            $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

            foreach ($sheetData as $key => $row) {
                if ($key < 4 || empty($row['A'])) {
                    continue;
                }

                $answers = [];
                $stt = $row['A'];
                $questionContent = $row['B'];
                $levelId = $row['C'];
                $answerA = $row['D'];
                $answerB = $row['E'];
                $answerC = $row['F'];
                $answerD = $row['G'];
                $correctAnswer = $row['H'];
                $gradeId = $row['I'];
                $unit = $row['J'];
                $suggest = $row['K'];
                $status = $row['L'];
                $teacherId = null;
                switch ($correctAnswer) {
                    case "A":
                        $answer = $answerA;
                        break;
                    case "B":
                        $answer = $answerB;
                        break;
                    case "C":
                        $answer = $answerC;
                        break;
                    default:
                        $answer = $answerD;
                }

                if (!empty($questionContent) && $teacherId == null) {
                    $question = new questions([
                        'subject_id' => $subjectId,
                        'question_content' => $questionContent,
                        'level_id' => $levelId,
                        'answer_a' => $answerA,
                        'answer_b' => $answerB,
                        'answer_c' => $answerC,
                        'answer_d' => $answerD,
                        'correct_answer' => $answer,
                        'grade_id' => $gradeId,
                        'unit' => $unit,
                        'suggest' => $suggest,
                        'status_id' => $status,
                        'teacher_id' => $teacherId,
                    ]);

                    // Lưu câu hỏi vào cơ sở dữ liệu
                    if ($question->saveQuietly()) {
                        $count++;
                    } else {
                        $errList[] = $row['A'];
                    }
                }
            }

            unlink($filePath);

            if (empty($errList)) {
                $result['status_value'] = "Thêm thành công " . $count . " câu hỏi!";
                $result['status'] = 1;
            } else {
                $result['status_value'] = "Lỗi! Không thể thêm câu hỏi có STT: " . implode(', ', $errList) . ', vui lòng xem lại.';
                $result['status'] = 0;
            }
        } else {
            $result['status_value'] = "Không tìm thấy tệp được tải lên!";
            $result['status'] = 0;
        }
        return response()->json($result);
    }

    public function checkAddQuestions(Request $request)
    {
        $result = [];

        $subjectId = $request->subject_id;
        $questionContent = $request->question_content;
        $gradeId = $request->grade_id;
        $levelId = $request->level_id;
        $unit = $request->unit;
        $answerA = $request->answer_a;
        $answerB = $request->answer_b;
        $answerC = $request->answer_c;
        $answerD = $request->answer_d;
        $status = $request->status_id;
        $suggest = $request->suggest;
        $correct_answer = $request->correct_answer;
        $teacherId = null;

        switch ($correct_answer) {
            case "A":
                $answer = $answerA;
                break;
            case "B":
                $answer = $answerB;
                break;
            case "C":
                $answer = $answerC;
                break;
            default:
                $answer = $answerD;
        }

        if (!empty($questionContent) && $teacherId == null) {
            $question = new questions([
                'subject_id' => $subjectId,
                'question_content' => $questionContent,
                'level_id' => $levelId,
                'answer_a' => $answerA,
                'answer_b' => $answerB,
                'answer_c' => $answerC,
                'answer_d' => $answerD,
                'correct_answer' => $answer,
                'grade_id' => $gradeId,
                'unit' => $unit,
                'suggest' => $suggest,
                'status_id' => $status,
                'teacher_id' => $teacherId,
            ]);
        }

        if ($question->save()) {
            $result = $question->toArray();
            $result['status_value'] = "Thêm thành công!";
            $result['status'] = 1;
        } else {
            $result['status_value'] = "Lỗi! câu hỏi đã tồn tại!";
            $result['status'] = 0;
        }
        return response()->json([
            'result' => $result,
        ]);
    }

    public function updateQuestions(Request $request)
    {
        $result = [];
        $question_id     = $request->question_id;
        if (!empty($question_id)) {
            $result['status_value'] = "Không tìm thấy câu hỏi!";
            $result['status'] = 0;
        }

        $question_content = $request->question_content;
        $grade_id        = $request->grade_id;
        $subject_id      = $request->subject_id;
        $level_id        = $request->level_id;
        $unit            = $request->unit;
        $answer_a        = $request->answer_a;
        $answer_b        = $request->answer_b;
        $answer_c        = $request->answer_c;
        $answer_d        = $request->answer_d;
        $status_id       = $request->status_id;
        $suggest         = $request->suggest;
        $correct_answer  = $request->correct_answer;

        switch ($correct_answer) {
            case "A":
                $answer = $answer_a;
                break;
            case "B":
                $answer = $answer_b;
                break;
            case "C":
                $answer = $answer_c;
                break;
            default:
                $answer = $answer_d;
        }

        if (empty($question_content) || empty($grade_id) || empty($unit) || empty($level_id) || empty($answer_a) || empty($answer_b) || empty($answer_c) || empty($answer_d) || empty($correct_answer)) {
            $result['status_value'] = "Không được bỏ trống các trường nhập!";
            $result['status'] = 0;
        } else {
            $question = questions::find($question_id);
            // dd($question);
            if ($question) {
                $question->update([
                    'subject_id' => $subject_id,
                    'question_content' => $question_content,
                    'level_id' => $level_id,
                    'grade_id' => $grade_id,
                    'unit' => $unit,
                    'answer_a' => $answer_a,
                    'answer_b' => $answer_b,
                    'answer_c' => $answer_c,
                    'answer_d' => $answer_d,
                    'correct_answer' => $answer,
                    'suggest' => $suggest,
                    'status_id' => $status_id,
                ]);

                $result['status_value'] = "Sửa thành công!";
                $result['status'] = 1;
            } else {
                $result['status_value'] = "Câu hỏi không tồn tại!";
                $result['status'] = 0;
            }
        }

        return response()->json($result);
    }

    public function deleteQuestion(Request $request)
    {
        $question_id = $request->question_id;
        $question = questions::find($question_id);

        if (!empty($question)) {
            $question->delete();
            return response()->json([
                'status'    => true,
                'message'   => 'Xoá câu hỏi thành công!',
            ]);
        } else {
            return response()->json([
                'status'    => false,
                'message'   => 'Xoá câu hỏi không thành công!',
            ]);
        }
    }

    public function checkAddTest(Request $request)
    {

        $result = [];

        $testName   = $request->test_name;
        $password   = bcrypt($request->password);
        $gradeId    = $request->grade_id;
        $subjectId  = $request->subject_id;
        $levelId    = $request->level_id;
        $totalQuestions = $request->total_questions;
        $timeToDo   = $request->time_to_do;
        $note       = $request->note;
        $testCode   = rand(100000, 999999);
    }
}
