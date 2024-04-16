<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\Student\CreateFileStudentRequest;
use App\Http\Requests\Admin\Student\CreateStudentRequest;
use App\Http\Requests\Admin\Student\DeleteStudentRequest;
use App\Http\Requests\Admin\Student\UpdateStudentRequest;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\students;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class AdminHSController extends Controller
{
     // quản lý hojc sinh
     public $successStatus = 200;
     public function index()
     {
         $data = students::get();
         if(empty($data)){
             return response()->json([
                 'data' => $data
             ]);}
         return response()->json([
             'data' => $data,
         ]);
     }

    public function submitLogin(LoginRequest $request)
    {
        $result = [];

        if ($request->has('email') && $request->has('password')) {
            $email = $request->input('email');
            $password = $request->input('password');


            $student = DB::table('students')
                ->select('permission')
                ->where('email', $email)
                ->orWhere('email', $password)
                ->first();

            if ($student) {
                $permission = $student->permission;
            }

            $token  = Auth::guard('apiStudents')->attempt([
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

    public function check_add_hs_via_file(CreateFileStudentRequest $request)
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
                $hs = new students([
                    'name' => $name,
                    'username' => $username,
                    'email' => $email,
                    'password' => $password,
                    'birthday' => $birthday,
                    'gender_id' => $gender,
                    'last_login' => now(),
                ]);

                if ($hs->saveQuietly()) {
                    $count++;
                } else {
                    $errList[] = $row['A'];
                }
            }
            //Xóa tệp
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
     public function createHS(Request $request)
     {
        // $result = [];
        $data = request()->only([
            'name',
            'username',
            'email',
            'password',
            'birthday',
            'last_login',
            'class_id',
            'gender_id']);
            $data['password'] = bcrypt($data['password']);
            $student = new students($data);
            $student->save();
         return response()->json([
            'student' => $student,
        ]);
     }

    public function deleteHS(DeleteStudentRequest $request)
    {
        $hs = students::find($request->student_id);
        if ($hs) {
            $hs->delete();
            return response()->json([
                'status'    => true,
                'message'   => 'Xoá học sinh thành công!',
            ]);
        } else {
            return response()->json([
                'status'    => false,
                'message'   => 'Không tìm thấy học sinh!',
            ], 404);
        }
    }



    public function updateHS(UpdateStudentRequest $request)
    {
        $hs = students::find($request->student_id);
        if ($hs) {
            $data = $request->all();
            $hs->update($data);

            return response()->json([
                'status'    => true,
                'message'   => 'Cập nhật học sinh thành công!',
            ]);
        } else {
            return response()->json([
                'status'    => false,
                'message'   => 'Không tìm thấy học sinh!',
            ]);
        }
    }
}
