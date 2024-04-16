<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\Subject_head\CreateFileTBMRequest;
use App\Http\Requests\Admin\Subject_head\CreateTBMRequest;
use App\Http\Requests\Admin\Subject_head\DeleteTBMRequest;
use App\Http\Requests\Admin\Subject_head\UpdateTBMRequest;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\subject_head;
use App\Models\subjects;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class AdminTBMonController extends Controller
{
    // quản lý trưởng bộ môn
    public $successStatus = 200;
    public function index()
    {
        $getAllTBM = subject_head::all();
        if ($getAllTBM->isEmpty()) {
            return response()->json([
                'message' => 'No data found',
            ], 400);
        }
        return response()->json([
            'message' => 'success',
            'data' => $getAllTBM
        ]);
    }

    public function submitLogin(LoginRequest $request)
    {

        if ($request->has('email') && $request->has('password')) {
            $email = $request->input('email');
            $password = $request->input('password');


            $teacher = DB::table('subject_head')
                ->select('permission')
                ->where('email', $email)
                ->orWhere('email', $password)
                ->first();

            if ($teacher) {
                $permission = $teacher->permission;
            }

            $token  = Auth::guard('apiTBM')->attempt([
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

    public function check_add_tbm_via_file(Request $request)
    {
        $result = [];
        if (!$request->hasFile('file'))  return response()->json([
            'message' => 'Chua nhap file',
        ], 400);

            $filePath = $request->file('file')->path();

            $reader = IOFactory::createReader('Xlsx');
            $spreadsheet = $reader->load($filePath);
            $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

            $count = 0;
            $errList = [];

            foreach ($sheetData as $key => $row) {
                if ($key < 4 ||empty($row['A'])) {
                    continue;
                }

                $name = $row['B'];
                $username = $row['C'];
                $email = $row['D'];
                $password = bcrypt($row['E']);
                $birthday = $row['F'];
                $gender = ($row['G'] == 'Nam') ? 1 : (($row['G'] == 'Nữ') ? 2 : 3);

                $subjectMappings = subjects::all()->pluck('id', 'name')->toArray();

                $subject = isset($subjectMappings[$row['H']]) ? $subjectMappings[$row['H']] : null;
                if ($subject === null) {
                    $errList[] = "Dòng $key: Môn học không hợp lệ";
                    continue;
                }
                $tbm = new subject_head([
                    'name' => $name,
                    'username' => $username,
                    'email' => $email,
                    'password' => $password,
                    'birthday' => $birthday,
                    'gender_id' => $gender,
                    'subject_id' => $subject,
                    'last_login' => now(),
                ]);

            try {
                $tbm->saveQuietly();
                $count++;
            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Thêm file không thành công',
                ], 400);
            }
        }
        unlink($filePath);
        return response()->json([
            "messagge"=> "Thêm thành công ". $count . " trưởng bộ môn",
        ]);
    }

    public function createTBM(Request $request)
    {
        $result = [];

        $name = $request->name;
        $username = $request->username;
        $password = bcrypt($request->password);
        $email = $request->email;
        $birthday = $request->birthday;
        $gender_id = $request->gender_id;
        $subject_id = $request->subject_id;

        // Kiểm tra xem tên người dùng đã tồn tại chưa
        $existingUser = subject_head::where('username', $username)->exists();

        if ($existingUser) {
            return response()->json([
                'status_value' => "Lỗi! Tài khoản đã tồn tại!",
                'status' => 0
            ]);
        }

        $tbm = new subject_head([
            'name' => $name,
            'username' => $username,
            'password' => $password,
            'email' => $email,
            'birthday' => $birthday,
            'gender_id' => $gender_id,
            'subject_id' => $subject_id,
            'last_login' => now(),
        ]);

        if ($tbm->save()) {
            $result = $tbm->toArray();
            $result['status_value'] = "Thêm thành công!";
            $result['status'] = 1;
        } else {
            $result['status_value'] = "Lỗi! Đã xảy ra lỗi khi lưu dữ liệu!";
            $result['status'] = 0;
        }

        return response()->json(['result' => $result]);
    }

    public function deleteTBM(Request $request)
    {
        $tbm = subject_head::findOrFail($request->id);
        // dd($tbm);
        if ($tbm) {
            $tbm->delete();
            return response()->json([
                'status'    => true,
                'message'   => 'Xoá trưởng bộ môn thành công!',
            ]);
        } else {
            return response()->json([
                'status'    => false,
                'message'   => 'Không tìm thấy trưởng bộ môn!',
            ], 404);
        }
    }



    public function updateTBM(Request $request)
    {
        $tbm = subject_head::find($request->subject_head_id);
        if ($tbm) {
            $data = $request->all();
            $tbm->update($data);

            return response()->json([
                'status'    => true,
                'message'   => 'Cập nhật trưởng bộ môn thành công!',
            ]);
        } else {
            return response()->json([
                'status'    => false,
                'message'   => 'Không tìm thấy trưởng bộ môn!',
            ]);
        }
    }
}
