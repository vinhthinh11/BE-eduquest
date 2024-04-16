<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\students;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;


class AdminHSController extends Controller
{
    // quản lý hojc sinh
    public $successStatus = 200;
    public function index()
    {
        $data = students::get();
        if (empty($data)) {
            return response()->json([
                'data' => $data
            ]);
        }
        return response()->json([
            'data' => $data,
        ]);
    }

    public function submitLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            //'username' => 'required|string|exists:admins,username',
            'email'    => 'required|email',
            'password' => 'required|string|max:20|min:6',
        ], [
            // 'username.required' => 'Tên đăng nhập là bắt buộc!',
            // 'username.exists'   => 'Tên đăng nhập không tồn tại!',
            'email.required'    => 'Email là bắt buộc!',
            'email.email'       => 'Email phải là định dạng hợp lệ!',
            'password.required' => 'Mật khẩu là bắt buộc!',
            'password.min'      => 'Mật khẩu tối thiểu 6 kí tự!',
            'password.max'      => 'Mật khẩu tối đa 20 kí tự!',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
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

    public function check_add_hs_via_file(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'          => 'required|string|min:6|max:50',
            'username'      => 'required|string|min:6|max:50|unique:students,username',
            'gender_id'     => 'required|integer',
            'password'      => 'required|string|min:6|max:20',
            'email'         => 'nullable|email|unique:students,email',
            'permission'    => 'nullable',
            'birthday'      => 'nullable|date',
            'file'          => 'required|file|mimes:xlsx,xls',
        ], [
            'name.min'              => 'Tên Học sinh tối thiểu 6 kí tự!',
            'name.required'         => 'Tên Học sinh không được để trống!',
            'username.required'     => 'Username không được để trống!',
            'username.unique'       => 'Username đã tồn tại!',
            'password.required'     => 'Password không được để trống!',
            'password.min'          => 'Password tối thiểu 6 kí tự!',
            'email.email'           => 'Email không đúng định dạng!',
            'email.unique'          => 'Email đã được sử dụng!',
            'birthday.date'         => 'Ngày Sinh phải là một ngày hợp lệ!',
            'file.*'                => 'File phải là một file định dạng xlsx hoặc xls!',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
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
        $validator = Validator::make($request->all(), [
            'name'          => 'required|string|min:6|max:50',
            'username'      => 'required|string|min:6|max:50|unique:students,username',
            'gender_id'     => 'required|integer',
            'password'      => 'required|string|min:6|max:20',
            'email'         => 'nullable|email|unique:students,email',
            'permission'    => 'nullable',
            'birthday'      => 'nullable|date',
        ], [
            'name.min'              => 'Tên Học Sinh tối thiểu 6 kí tự!',
            'name.required'         => 'Tên Học Sinh không được để trống!',
            'username.required'     => 'Username không được để trống!',
            'username.unique'       => 'Username đã tồn tại!',
            'password.required'     => 'Password không được để trống!',
            'password.min'          => 'Password tối thiểu 6 kí tự!',
            'email.email'           => 'Email không đúng định dạng!',
            'email.unique'          => 'Email đã được sử dụng!',
            'birthday.date'         => 'Ngày Sinh phải là một ngày hợp lệ!',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = request()->only([
            'name',
            'username',
            'email',
            'password',
            'birthday',
            'last_login',
            'class_id',
            'gender_id'
        ]);
        $data['password'] = bcrypt($data['password']);
        $student = new students($data);
        $student->save();
        return response()->json([
            'student' => $data,
        ]);
    }

    public function deleteHS(Request $request)
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



    public function updateHS(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,student_id',
            'name' => 'required|string|min:6|max:50',
            'gender_id' => 'required|integer',
            'birthday' => 'nullable|date',
            'password' => 'nullable|string|min:6|max:20',
        ], [
            'student_id.required' => 'Học Sinh không được để trống!',
            'student_id.exists' => 'Học Sinh không tồn tại!',
            'name.min' => 'Tên Học Sinh tối thiểu 6 kí tự!',
            'name.required' => 'Tên Học Sinh không được để trống!',
            'gender_id.required' => 'Giới tính không được để trống!',
            'birthday.date' => 'Ngày Sinh phải là một ngày hợp lệ!',
            'password.min' => 'Mật khẩu tối thiểu 6 kí tự!',
            'password.max' => 'Mật khẩu không được quá 20 kí tự!',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
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
