<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\subject_head;
use App\Models\subjects;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;

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
        $validator = Validator::make($request->all(), [
            'name'          => 'required|string|min:6|max:50',
            'username'      => 'required|string|min:6|max:50|unique:subject_head,username',
            'gender_id'     => 'required|integer',
            'password'      => 'required|string|min:6|max:20',
            'email'         => 'nullable|email|unique:subject_head,email',
            'permission'    => 'nullable',
            'birthday'      => 'nullable|date',
            'file'          => 'required|file|mimes:xlsx',
        ], [
            'name.min'           => 'Tên Trưởng bộ môn tối thiểu 6 kí tự!',
            'name.required'         => 'Tên Trưởng bộ môn không được để trống!',
            'username.required'     => 'Username không được để trống!',
            'username.unique'       => 'Username đã tồn tại!',
            'password.required'     => 'Password không được để trống!',
            'password.min'          => 'Password tối thiểu 6 kí tự!',
            'email.email'           => 'Email không đúng định dạng!',
            'email.unique'          => 'Email đã được sử dụng!',
            'birthday.date'         => 'Ngày Sinh phải là một ngày hợp lệ!',
            'file.required'         => 'Chưa nhập file!',
            'file.mimes'            => 'File nhập vào không hợp lệ!',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
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
                        "mesagge"=> "them thanh cong ". $count . " truong bo mon",
                    ]);
}
    public function createTBM(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'          => 'required|string|min:6|max:50|unique:subject_head,name',
            'username'      => 'required|string|min:6|max:50|unique:subject_head,username',
            'gender_id'     => 'required|integer',
            'password'      => 'required|string|min:6|max:20',
            'email'         => 'nullable|email|unique:subject_head,email',
            'permission'    => 'nullable',
            'birthday'      => 'nullable|date',
            'subject_id'      => 'required|integer',
        ], [
            'name.min'              => 'Tên Trưởng bộ môn tối thiểu 6 kí tự!',
            'name.max'              => 'Tên Trưởng bộ môn phải là 50 kí tự!',
            'name.unique'           => 'Tên Trưởng bộ môn đã tồn tại!',
            'username.min'          => 'Username tối thiểu 6 kí tự!',
            'name.required'         => 'Tên Trưởng bộ môn không được để trống!',
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

        $name = $request->name;
        $username = $request->username;
        $password = bcrypt($request->password);
        $email = $request->email;
        $birthday = $request->birthday;
        $gender_id = $request->gender_id;
        $subject_id = $request->subject_id;

        // Kiểm tra xem tên người dùng đã tồn tại chưa

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
        $validator = Validator::make($request->all(), [
            'subject_head_id' => 'required|exists:subject_head,subject_head_id'
        ], [
            'subject_head_id.*' => 'Trưởng bộ môn không tồn tại!',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        $tbm = subject_head::find($request->subject_head_id)->delete();
            return response()->json([
                'tbm'    => $tbm,
                'message'   => 'Xoá trưởng bộ môn thành công!']);
        }




    public function updateTBM(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subject_head_id' => 'required|exists:subject_head,subject_head_id',
            'name' => 'required|string|min:6|max:50',
            'gender_id' => 'required|integer',
            'birthday' => 'nullable|date',
            'password' => 'nullable|string|min:6|max:20',
        ], [
            'subject_head_id.required' => 'Trưởng bộ môn không được để trống!',
            'student_id.exists' => 'Trưởng bộ môn không tồn tại!',
            'name.min' => 'Tên Trưởng bộ môn tối thiểu 6 kí tự!',
            'name.required' => 'Tên Trưởng bộ môn không được để trống!',
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
