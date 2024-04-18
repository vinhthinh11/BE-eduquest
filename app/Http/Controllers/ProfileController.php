<?php

namespace App\Http\Controllers;

use App\Models\admin;
use App\Models\student;
use App\Models\students;
use App\Models\subject_head;
use App\Models\teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;


class ProfileController extends Controller
{
    // public function updateProfile(Request $request)
    // {
    //     $data = [
    //         'id' => $request->id,
    //     ];

    //     $models = [
    //         Admin::class => 'admins',
    //         Student::class => 'students',
    //         Teacher::class => 'teachers',
    //         Subject_Head::class => 'subject_head',
    //     ];

    //     $modelName = $request->admin_id ? Admin::class : $request->student_id ? Student::class : $request->teacher_id ? Teacher::class : $request->subject_head_id ? Subject_Head::class : null;
    //     if (!isset($models[$modelName])) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Model không hợp lệ!',
    //         ], 400);
    //     }

    //     $rules = [];

    //     foreach ($models as $model) {
    //         $rules[$model] = [
    //             'email' => 'nullable|email|unique:' . $model . ',' . 'email,' . $data['id'] . ',' . Str::singular($model) . '_id',
    //         ];
    //     }

    //     $validation = Validator::make($data, $rules);
    //     $validator = Validator::make($request->all(), [
    //         'name' => 'required|min:3|max:255',
    //         'gender_id' => 'required',
    //         'birthday' => 'nullable|date',
    //         'password' => 'required|min:6|max:20',
    //     ], [
    //         'name.required' => 'Vui lòng nhập tên!',
    //         'name.min' => 'Tên cần ít nhất 3 ký tự!',
    //         'name.max' => 'Tên dài nhất 255 ký tự!',
    //         'gender_id.required' => 'Vui lòng chọn giới tính!',
    //         'birthday.date' => 'Ngày sinh chưa đúng định dạng!',
    //         'password.required' => 'Vui lòng nhập mật khẩu!',
    //         'password.min' => 'Vui lòng nhập ít nhất 6 ký tự!',
    //         'email.email' => 'Vui lòng nhập email hợp lệ!',
    //         'email.unique' => 'Email đã tồn tại!',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'status' => false,
    //             'errors' => $validator->errors(),
    //         ], 422);
    //     }



    //     $modelClass = $models[$modelName];
    //     $me = $modelClass::find($request->id);
    //     $me->update([
    //         'name' => $request['name'],
    //         'email' => $request['email'],
    //         'gender_id' => $request['gender_id'],
    //         'birthday' => $request['birthday'],
    //         'password' => bcrypt($request['password']),
    //     ]);

    //     return response()->json([
    //         'status' => true,
    //         'message' => "Cập nhật tài khoản cá nhân thành công!",
    //     ]);
    // }




    //     $data = $request->validated();

        // $currentUser = Auth::user();

        // if ($user->id !== $currentUser->id && !$currentUser->isAdmin() && !($currentUser->isTeacher() || $currentUser->isStudent())) {
        //     return response()->json(['status_value' => 'Bạn không có quyền thay đổi thông tin của người dùng khác!', 'status' => 0]);
        // }

    //     $user->update([
    //         'name' => $data['name'],
    //         'email' => $data['email'],
    //         'gender_id' => $data['gender_id'],
    //         'birthday' => $data['birthday'],
    //         'password' => bcrypt($data['password']),
    //     ]);

    //     return response()->json(['status_value' => 'Cập nhật thông tin tài khoản thành công!', 'status' => 1]);
    // }

    // public function updateProfile(Request $request)
    // {
    //     $username = $request->input('username');
    //     $name = $request->input('name');
    //     $email = $request->input('email');
    //     $password = password_hash($request->input('password'), PASSWORD_BCRYPT);
    //     $gender_id = $request->input('gender_id');
    //     $birthday = $request->input('birthday');

    //     $result = DB::table('admins')
    //         ->where('username', $username)
    //         ->update([
    //             'email' => $email,
    //             'password' => $password,
    //             'name' => $name,
    //             'gender_id' => $gender_id,
    //             'birthday' => $birthday
    //         ]);

    //         if(!$result){
    //             return response()->json([
    //                 'result' => $result,
    //                 'status'    => false,
    //                 'message'   => 'Có lỗi xảy ra!'
    //             ]);
    //         }
    //     return response()->json([
    //         'kq'    => $result,
    //         'status'    => true,
    //         'message'   => 'Cập Nhập thông tin tài khoản thành công!',

    //     ]);
    // }

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

    public function updateAvatarProfile(Request $request)
    {
        $admin = Admin::find($request->id);

        if (!$admin) {
            return response()->json([
                'status' => false,
                'message' => 'Admin không tồn tại!',
            ], 404);
        }

        if ($request->hasFile('avatar')) {
            $validator = Validator::make($request->all(), [
                'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ], [
                'avatar.required' => 'Vui lòng chọn hình ảnh đại diện',
                'avatar.image' => 'Vui lòng chọn hình ảnh đại diện',
                'avatar.mimes' => 'Vui lòng chọn hình ảnh đúng định dạng (jpeg, png, jpg, gif, svg)',
                'avatar.max' => 'Kích thước hình ảnh không được vượt quá 2048KB',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $image = $request->file('avatar');
            $path = $image->store('images');
            $admin->avatar = $path;
            $admin->save();

            return response()->json(['message' => 'Tải lên thành công', 'path' => $path], 200);
        } else {
            return response()->json(['message' => 'Không có tệp nào được tải lên'], 404);
        }
    }
    // public function updateavatarProfile(Request $request)
    // {
    //     $hinhAnh = admin::find($request->hinh_anh);

    //     if ($request->hasFile('avatar')) {
    //         $image = $request->file('avatar');
    //         $path = $image->store('images');
    //         return response()->json(['message' => 'Tải lên thành công', 'path' => $path]);
    //     } else {
    //         return response()->json(['message' => 'Không có tệp nào được tải lên']);
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
    // public function updateLastLogin(Request $request, $data_id)
    // {
    //     // $adminId = $request->input('admin_id');

    //     // $result = DB::table('admins')
    //     //     ->where('admin_id', $adminId)
    //     //     ->update(['last_login' => now()]);
    //     DB::table('admins')
    //     ->where('admin_id', $admin_id)
    //     ->update(['last_login' => DB::raw('CURRENT_TIMESTAMP')]);

    //     return response()->json([
    //         'admin' => $admin_id,
    //     ]);
    // }

    // public function adminInfo(Request $request)
    // {
    //     if (!$request->has('username')) {
    //         return response()->json([
    //             'error' => 'Thiếu trường username trong request',
    //         ], 400);
    //     }

    //     $username = $request->input('username');

    //     if (!is_string($username) || empty($username)) {
    //         return response()->json([
    //             'error' => 'Giá trị của trường username không hợp lệ',
    //         ], 400);
    //     }

    //     $adminInfo = admin::select('admin_id', 'username', 'avatar', 'email', 'name', 'last_login', 'birthday', 'permissions.detail AS permission_detail', 'genders.detail AS gender_detail', 'genders.gender_id')
    //         ->join('permissions', 'admins.permission', '=', 'permissions.permission')
    //         ->join('genders', 'admins.gender_id', '=', 'genders.gender_id')
    //         ->where('admins.username', $username)
    //         ->first();

    //     if (!$adminInfo) {
    //         return response()->json([
    //             'error' => 'Không tìm thấy thông tin admin',
    //         ], 404);
    //     }

    //     return response()->json([
    //         'adminInfo' => $adminInfo,
    //     ]);
    // }

    public function getProfiles(Request $request)
    {
        $username = $request->input('username');
        $admin = new Admin();
        $adminInfo = $admin->getAdminInfo($username);

        return response()->json([
            'adminInfo' => $adminInfo
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
