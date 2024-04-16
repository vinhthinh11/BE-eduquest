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
use Validator;


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
               return response()->json([
            'result' =>  $result,
            'access_token' => $token,
            'expires_in' => JWTAuth::factory()->getTTL() * 6000
               ]);
            } else {
                return response()->json([
            'mesage' =>  "Tài khoản hoặc mật khẩu không đúng!",
        ],403);
            }
        }
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
        $admin = admin::find($request->id);

        if(!$admin) {
             return response()->json([
                'message'   => 'Admin không tồn tại!'
            ],400);
            }
              $admin->delete();
                return response()->json([
                    'message'   => 'Xóa Admin thành công!',
                ]);
        }


    public function updateAdmin(Request $request)
    {
          $admin = Admin::find($request->admin_id);
        $data = $request->only(['name', 'username','gender_id', 'birthday', 'password','permission',]);

        if (!$admin) {
             return response()->json([
                'status'    => false,
                'message'   => 'Tài khoản không tồn tại!'
            ],400);
        }
        else if (isset($data['password'])) {
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
       $question = questions::find($request->question_id);
    if(empty($question)) {
        return response()->json(["message" => "Không tìm thấy câu hỏi!"], 400);
    }
       $validator = Validator::make($request->all(),[
 'question_content'=>'string|max:255',
        'level_id'=>'numeric',
        'answer_a'=>'string',
        'answer_b'=>'string',
        'answer_c'=>'string',
        'answer_d'=>'string',
        'correct_answer'=>'numeric',
        'grade_id'=>'numeric',
        'unit'=>'numeric',
        'suggest'=>'string',
        'status_id'=>'numeric',
        'teacher_id'=>'numeric',
       ]);
       if($validator->fails()){
        return response($validator->errors()->all(),400);
       }
       $data = $request->only(['question_content','level_id','answer_a','answer_b','answer_c','answer_d','correct_answer','grade_id','unit','suggest','status_id','teacher_id']);
      $question->fill($data);
    //   $question->fill($data)->save();
    return response()->json(["question" => $question]);
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
