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

    public function check_add_tbm_via_file(CreateFileTBMRequest $request)
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
                $subject = ($row['H'] == 'Toán') ? 1 : (($row['H'] == 'Ngữ Văn') ? 2 :  3);
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
                    'message' => 'Them file khong thanh cong',
                ], 400);
            }
        }
                    unlink($filePath);
                    return response()->json([
                        "mesagge"=> "them thanh cong ". $count . " truong bo mon",
                    ]);
}
    public function createTBM(CreateTBMRequest $request)
    {
        $result = [];

        $name = $request->input('name');
        $username = $request->input('username');
        $password = bcrypt($request->input('password'));
        $email = $request->input('email');
        $birthday = $request->input('birthday');
        $gender = $request->input('gender');
        $subject = $request->input('subject');

        //giới tính
        if ($gender == 'Nam') {
            $gender_id = 2;
        } else if ($gender == 'Nữ') {
            $gender_id = 3; // Hoặc bất kỳ giá trị khác tương ứng với giới tính Nam
        } else {
            $gender_id = 1;
        }

        // Danh sách các môn học
        $subjects = [
            1 => 'Toán',
            2 => 'Ngữ Văn',
            3 => 'Lịch sử',
            4 => 'Địa Lý',
            5 => 'Vật Lý',
            6 => 'Công nghệ',
            7 => 'GDCD',
            8 => 'Anh',
            9 => 'Hóa học',
            10 => 'Sinh học'
        ];
        // Chọn một môn học ngẫu nhiên
        $chosen_subject_id = array_rand($subjects);
        $chosen_subject = $subjects[$chosen_subject_id];

        $tbm = new subject_head([
            'name' => $name,
            'username' => $username,
            'password' => $password,
            'email' => $email,
            'birthday' => $birthday,
            'gender_id' => $gender_id,
            'subject_id' => $chosen_subject_id,
            'last_login' => now(),

        ]);

        // Lưu TBM  mới vào cơ sở dữ liệu
        if ($tbm->save()) {
            $result = $tbm->toArray();
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

    public function deleteTBM(DeleteTBMRequest $request)
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



    public function updateTBM(UpdateTBMRequest $request)
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
