<?php

namespace App\Http\Controllers;

use App\Models\admin;
use App\Models\questions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Admin\CreateAdminRequest;
use App\Http\Requests\Admin\Admin\CreateFileAdminRequest;
use App\Http\Requests\Admin\Admin\DeleteAdminRequest;
use App\Http\Requests\Admin\Admin\UpdateAdminRequest;
use App\Http\Requests\Admin\Question\CreateFileQuestionRequest;
use App\Http\Requests\Admin\Question\CreateQuestionRequest;
use App\Http\Requests\Admin\Question\DeleteQuestionRequest;
use App\Http\Requests\Admin\Question\UpdateQuestionRequest;
use App\Http\Requests\LoginRequest;
use App\Models\grade;
use App\Models\level;
use App\Models\status;
use App\Models\student;
use App\Models\students;
use App\Models\subjects;
use App\Models\tests;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;


class Admincontroller extends Controller
{
    public function getAdmin()
    {
        $data = admin::get();
        if ($data->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No admin found!',
            ], 400);
        }
        return response()->json([
            'data'    => $data,
        ]);
    }

    public function indexLogin()
    {
        return view('loginTest');
    }

    // public function __construct()
    // {
    //     $this->middleware('auth:api', ['except' => ['submitLogin']]);
    // }

    public function submitLogin(LoginRequest $request)
    {
        $result = [];

        if ($request->has('email') && $request->has('password')) {
            $email = $request->input('email');
            $password = $request->input('password');


            $admin = DB::table('admins')
                ->select('permission')
                ->where('email', $email)
                ->orWhere('email', $password)
                ->first();

            if ($admin) {
                $permission = $admin->permission;
            }

            $token  = Auth::guard('api')->attempt([
                'email'    => $email,
                'password'    => $password,
            ]);
            // dd($token);
            if ($token) {
                return response()->json([
                    'result' =>  "Đăng nhập thành công",
                    'access_token' => $token,
                    'permission' => $permission,
                    'expires_in' => JWTAuth::factory()->getTTL() * 6000
                ]);
            } else {
                return response()->json([
                    'mesage' =>  "Tài khoản hoặc mật khẩu không đúng!",
                ], 403);
            }
        }
    }


    public function logout(Request $request)
    {
        Auth::guard('api')->logout();
        return redirect('api/admin/login');
    }


    public function check_add_admin_via_file(CreateFileAdminRequest $request)
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
    public function createAdmin(CreateAdminRequest $request)
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

    public function deleteAdmin(DeleteAdminRequest $request)
    {
        $admin = admin::find($request->id);

        if (!$admin) {
            return response()->json([
                'message'   => 'Admin không tồn tại!'
            ], 400);
        }
        $admin->delete();
        return response()->json([
            'message'   => 'Xóa Admin thành công!',
            "admin" => $admin
        ]);
    }


    public function updateAdmin(UpdateAdminRequest $request)
    {
        $admin = Admin::find($request->admin_id);
        $data = $request->only(['name', 'username', 'gender_id', 'birthday', 'password', 'permission',]);

        if (!$admin) {
            return response()->json([
                'status'    => false,
                'message'   => 'Tài khoản không tồn tại!'
            ], 400);
        } else if (isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }

        $admin->fill($data)->save();

        return response()->json([
            'message'   => 'Cập nhật thông tin thành công!',
            'admin'   => $admin,
        ]);
    }

    public function getQuestion()
    {
        $data = questions::with('teacher')->get();
        if(!$data)return response()->json([
            'message' => 'No question found!',
        ], 400);
        return response()->json([
            'data' => $data,
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


    public function checkAddQuestionViaFile(CreateFileQuestionRequest $request)
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
                        'status_id' => 3,
                        'teacher_id' => $teacherId,
                    ]);

                    // Lưu câu hỏi vào cơ sở dữ liệu
                    if ($question->saveQuietly()) {
                        $count++;
                    } else {
                        $errList[] = $stt;
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

    public function checkAddQuestions(CreateQuestionRequest $request)
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

    public function updateQuestions(UpdateQuestionRequest $request)
    {
       $question = questions::find($request->question_id);
       if(!$question)return response()->json(["message" => "Không tìm thấy câu hỏi!"], 400);
       $data = $request->only(['question_content', 'level_id', 'answer_a', 'answer_b', 'answer_c', 'answer_d', 'correct_answer', 'grade_id', 'unit','suggest','status_id', 'teacher_id']);
      $updateQuestion  =$question->fill($data)->save();
    return response()->json(["updateData" => $updateQuestion]);
        }



    public function deleteQuestion(Request $request)
    {
        $question_id = $request->question_id;
        $question = questions::find($question_id);

        if (!$question) {
            return response()->json([
                'status'    => false,
                'message'   => 'Xoá câu hỏi không thành công!',
            ]);

        }
        $question->delete();
        return response()->json([
            'status'    => true,
            'message'   => 'Xoá câu hỏi thành công!',
        ]);
        // return response()->json($question_id);
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
        $questionEasy = $request->question_easy;
        $questionAverage = $request->question_average;
        $questionDifficult = $request->question_difficult;
        $timeToDo   = $request->time_to_do;
        $note       = $request->note;
        $testCode   = rand(100000, 999999);
        $teacher    = new admin();
        $total      = $teacher->getCountQuestions($subjectId, $gradeId);

        if (empty($testName) || empty($timeToDo) || empty($password)) {
            $result['status_value'] = "Không được bỏ trống các trường nhập!";
            $result['status'] = 0;
        } else {
            if ($totalQuestions != null) {
                if ($totalQuestions > $total->question_count) {
                    $result['status_value'] = "Số lượng câu hỏi môn " . $total->subject_detail . " " . $total->grade_detail . " không đủ! Vui lòng nhập số lượng tối đa " . $total->question_count . " câu hỏi!";
                    $result['status'] = 0;
                    if ($total->question_count == 0) {
                        $result['status_value'] = "Không có câu hỏi nào trong ngân hàng câu hỏi cho môn " . $total->subject_detail . " " . $total->grade_detail . "!";
                    }
                } else {
                    $test = new Tests([
                        'test_name' => $testName,
                        'password' => $password,
                        'subject_id' => $subjectId,
                        'grade_id' => $gradeId,
                        'level_id' => $levelId,
                        'total_questions' => $totalQuestions,
                        'time_to_do' => $timeToDo,
                        'note' => $note,
                        'status_id' => 3,
                    ]);
                    $test->saveQuietly();

                    if ($test) {
                        $result['status_value'] = "Thêm thành công!";
                        $result['status'] = 1;

                        $adminModel = new Admin();
                        $limit = $adminModel->calculateQuestionLevel($totalQuestions, $levelId);
                        foreach ($limit as $levelId => $limitQuest) {
                            $listQuest = $adminModel->getListQuestByLevel($gradeId, $subjectId, $levelId, $limitQuest);
                            foreach ($listQuest as $quest) {
                                $adminModel->addQuestToTest($test->test_code, $quest->question_id);
                            }
                        }
                    } else {
                        $result['status_value'] = "Thêm thất bại!";
                        $result['status'] = 0;
                    }
                }
            } else {
                $totalQuestions = $questionEasy + $questionAverage + $questionDifficult;
                if ($totalQuestions > $total->question_count) {
                    $result['status_value'] = "Số lượng câu hỏi môn " . $total->subject_detail . " " . $total->grade_detail . " không đủ! Vui lòng nhập số lượng tối đa " . $total->question_count . " câu hỏi!";
                    $result['status'] = 0;
                    if ($total->question_count == 0) {
                        $result['status_value'] = "Không có câu hỏi nào trong ngân hàng câu hỏi cho môn " . $total->subject_detail . " " . $total->grade_detail . "!";
                    }
                } else {
                    $test = new Tests([
                        'test_name' => $testName,
                        'password' => $password,
                        'subject_id' => $subjectId,
                        'grade_id' => $gradeId,
                        'level_id' => $levelId,
                        'total_questions' => $totalQuestions,
                        'time_to_do' => $timeToDo,
                        'note' => $note,
                        'status_id' => 3,
                    ]);
                    $test->saveQuietly();
                    if ($test) {
                        $result['status_value'] = "Thêm thành công!";
                        $result['status'] = 1;
                        //Tạo bộ câu hỏi cho đề thi
                        $adminModel = new admin();
                        $limit = $adminModel->caculatorQuestionNormal($questionEasy, $questionAverage, $questionDifficult);
                        foreach ($limit as $levelId => $limitQuest) {
                            $listQuest = $adminModel->getListQuestByLevel($gradeId, $subjectId, $levelId, $limitQuest);
                            foreach ($listQuest as $quest) {
                                $adminModel->addQuestToTest($test->test_code, $quest->question_id);
                            }
                        }
                    } else {
                        $result['status_value'] = "Thêm thất bại!";
                        $result['status'] = 0;
                    }
                }
            }
        }
        return response()->json([
            'result' => $result,

        ]);
    }

    public function addTest(Request $request)
    {
        $result   = [];
        $student  = new student();
        $testCode = $request->test_code;
        $password = md5($request->password);
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
                        $student->addStudentQuest(2, $ID, $testCode, $quest->question_id, $array[0], $array[1], $array[2], $array[3]);
                    } else {
                        $result['status_value'] = "Không có đáp án";
                        $result['status'] = 0;
                    }
                    $student->updateStudentExam($testCode, $time, 2);
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
            // 'listQuest' => $listQuest,
        ]);
    }
}
