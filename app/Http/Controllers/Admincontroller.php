<?php

namespace App\Http\Controllers;

use App\Models\admin;
use App\Models\questions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Http\Controllers\Controller;
use App\Models\student;
use App\Models\subject_head;
use App\Models\teacher;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
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


    public function checkAddQuestionViaFile(Request $request)
    {
        $result = [];

        $subjectId = 10;
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

                if (!empty($questionContent)) {
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
                        'teacher_id' => $teacherId,
                        'suggest' => 'Nội dung gợi ý',
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
}
