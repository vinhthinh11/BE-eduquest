<?php

namespace App\Http\Controllers;

use App\Models\admin;
use App\Models\student;
use App\Models\subject_head;
use App\Models\teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToArray;

class ProfileController extends Controller
{

    // public function updateProfile(Request $request)
    // {
    //     $data = $request->all();

    //     if (empty($data['name']) || empty($data['gender_id']) || empty($data['birthday']) || empty($data['password']) || empty($data['email'])) {
    //         $result['status_value'] = "Không được bỏ trống các trường nhập!";
    //         $result['status'] = 0;
    //     } else {
    //         $admin = admin::where('username', $data['username'])->first();
    //         if (!$admin) {
    //             $result['status_value'] = "Tài khoản không tồn tại!";
    //             $result['status'] = 0;
    //         } else {
    //             $admin->fill([
    //                 'name' => $data['name'],
    //                 'email' => $data['email'],
    //                 'gender_id' => $data['gender_id'],
    //                 'birthday' => $data['birthday'],
    //                 'password' => bcrypt($data['password']),
    //             ])->save();

    //             $result = $admin->toArray();
    //             $result['status_value'] = "Sửa thành công!";
    //             $result['status'] = 1;
    //         }
    //     }

    //     return response()->json($result);
    // }

    public function updateProfile(Request $request)
    {
        $username = $request->input('username');
        $name = $request->input('name');
        $email = $request->input('email');
        $password = password_hash($request->input('password'), PASSWORD_BCRYPT);
        $gender_id = $request->input('gender_id');
        $birthday = $request->input('birthday');

        $result = DB::table('admins')
            ->where('username', $username)
            ->update([
                'email' => $email,
                'password' => $password,
                'name' => $name,
                'gender_id' => $gender_id,
                'birthday' => $birthday
            ]);

            if(!$result){
                return response()->json([
                    'result' => $result,
                    'status'    => false,
                    'message'   => 'Có lỗi xảy ra!'
                ]);
            }
        return response()->json([
            'kq'    => $result,
            'status'    => true,
            'message'   => 'Cập Nhập thông tin tài khoản thành công!',

        ]);
    }

    // public function submitUpdateAvatar(Request $request)
    // {
    //     $username = $request->input('username');

    //     if ($request->hasFile('file')) {
    //         $file = $request->file('file');
    //         $extension = $file->getClientOriginalExtension();

    //         if (in_array($extension, ['jpg', 'png'])) {
    //             $fileName = $username . '_' . $file->getClientOriginalName();
    //             $path = 'res/img/avatar/' . $fileName;

    //             if ($file->move(public_path('res/img/avatar'), $fileName)) {
    //                 $update = $this->update_avatar($fileName, $username);
    //                 // Xử lý khi update thành công
    //             } else {
    //                 // Xử lý khi di chuyển file không thành công
    //             }
    //         } else {
    //             // Xử lý khi định dạng file không hợp lệ
    //         }
    //     } else {
    //         // Xử lý khi không có file được gửi
    //     }
    // }


    public function updateAvatar(Request $request)
    {
        $avatar = $request->input('avatar');
        $username = $request->input('username');

        $result = DB::table('admins')
            ->where('username', $username)
            ->update(['avatar' => $avatar]);

        if(!$result){
            return response()->json([
                'result' => $result,
                'status'    => false,
                'message'   => 'Có lỗi xảy ra!'
            ]);
        }
        return response()->json([
            'result' => $result,
            'status'    => true,
            'message'   => 'Cập Nhập avatar thành công!'
        ]);
    }

    public function updateLastLogin(Request $request)
    {
        $adminId = $request->input('admin_id');

        $result = DB::table('admins')
            ->where('admin_id', $adminId)
            ->update(['last_login' => now()]);

        return response()->json([
            'result' => $result,

        ]);
    }

    public function adminInfo(Request $request)
    {
        if (!$request->has('username')) {
            return response()->json([
                'error' => 'Thiếu trường username trong request',
            ], 400);
        }

        $username = $request->input('username');

        if (!is_string($username) || empty($username)) {
            return response()->json([
                'error' => 'Giá trị của trường username không hợp lệ',
            ], 400);
        }

        $adminInfo = admin::select('admin_id', 'username', 'avatar', 'email', 'name', 'last_login', 'birthday', 'permissions.detail AS permission_detail', 'genders.detail AS gender_detail', 'genders.gender_id')
            ->join('permissions', 'admins.permission', '=', 'permissions.permission')
            ->join('genders', 'admins.gender_id', '=', 'genders.gender_id')
            ->where('admins.username', $username)
            ->first();

        if (!$adminInfo) {
            return response()->json([
                'error' => 'Không tìm thấy thông tin admin',
            ], 404);
        }

        return response()->json([
            'adminInfo' => $adminInfo,
        ]);
    }
    public function studentInfo(Request $request)
    {
        if (!$request->has('username')) {
            return response()->json([
                'error' => 'Thiếu trường username trong request',
            ], 400);
        }

        $username = $request->input('username');

        if (!is_string($username) || empty($username)) {
            return response()->json([
                'error' => 'Giá trị của trường username không hợp lệ',
            ], 400);
        }

        $student = student::select(
            'students.student_id as ID',
            'students.username',
            'students.name',
            'students.email',
            'students.avatar',
            'students.class_id',
            'students.birthday',
            'students.last_login',
            'genders.gender_id',
            'genders.gender_detail',
            'classes.grade_id',
            'students.doing_exam',
            'students.time_remaining',
            'students.doing_practice',
            'students.practice_time_remaining'
        )
        ->join('genders', 'genders.gender_id', '=', 'students.gender_id')
        ->join('classes', 'classes.class_id', '=', 'students.class_id')
        ->where('students.username', $username)
        ->first(); 


        if (!$student) {
            return response()->json([
                'error' => 'Không tìm thấy thông tin Học Sinh',
            ], 404);
        }

        return response()->json([
            'student' => $student,
        ]);
    }

    public function teacherInfo(Request $request)
    {
        if (!$request->has('username')) {
            return response()->json([
                'error' => 'Thiếu trường username trong request',
            ], 400);
        }

        $username = $request->input('username');

        if (!is_string($username) || empty($username)) {
            return response()->json([
                'error' => 'Giá trị của trường username không hợp lệ',
            ], 400);
        }

        $teacher = teacher::select(
            'teachers.teacher_id as ID',
            'teachers.username',
            'teachers.name',
            'teachers.email',
            'teachers.avatar',
            'teachers.birthday',
            'teachers.last_login',
            'genders.gender_id',
            'genders.gender_detail'
        )
        ->join('genders', 'genders.gender_id', '=', 'teachers.gender_id')
        ->where('teachers.username', $username)
        ->first();

        if (!$teacher) {
            return response()->json([
                'error' => 'Không tìm thấy thông tin Giáo Viên',
            ], 404);
        }

        return response()->json([
            'teacher' => $teacher,
        ]);
    }

    public function subjectheadInfo(Request $request)
    {
        if (!$request->has('username')) {
            return response()->json([
                'error' => 'Thiếu trường username trong request',
            ], 400);
        }

        $username = $request->input('username');

        if (!is_string($username) || empty($username)) {
            return response()->json([
                'error' => 'Giá trị của trường username không hợp lệ',
            ], 400);
        }

        $subjectHead = subject_head::select(
            'subject_head.subject_head_id',
            'subject_head.username',
            'subject_head.avatar',
            'subject_head.email',
            'subject_head.name',
            'subject_head.last_login',
            'subject_head.birthday',
            'permissions.permission_detail',
            'genders.gender_detail',
            'genders.gender_id'
        )
        ->join('permissions', 'subject_head.permission', '=', 'permissions.permission')
        ->join('genders', 'subject_head.gender_id', '=', 'genders.gender_id')
        ->where('subject_head.username', $username)
        ->first();

        if (!$subjectHead) {
            return response()->json([
                'error' => 'Không tìm thấy thông tin TBM',
            ], 404);
        }

        return response()->json([
            'TBM' => $subjectHead,
        ]);
    }

}