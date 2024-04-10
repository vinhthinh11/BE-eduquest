<?php

namespace App\Http\Controllers;

use App\Models\admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AdminProfileController extends Controller
{
    public function getProfiles(Request $request)
    {
        $userInfo = $request->session()->get('user_info');

        return response()->json([
            'userInfo' => $userInfo
        ]);
    }

    public function updateProfile(Request $request)
    {
        $data = $request->all();

        if (empty($data['name']) || empty($data['gender_id']) || empty($data['birthday']) || empty($data['password']) || empty($data['email'])) {
            $result['status_value'] = "Không được bỏ trống các trường nhập!";
            $result['status'] = 0;
        } else {
            $admin = admin::where('username', $data['username'])->first();
            if (!$admin) {
                $result['status_value'] = "Tài khoản không tồn tại!";
                $result['status'] = 0;
            } else {
                $admin->fill([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'gender_id' => $data['gender_id'],
                    'birthday' => $data['birthday'],
                    'password' => bcrypt($data['password']),
                ])->save();

                $result = $admin->toArray();
                $result['status_value'] = "Sửa thành công!";
                $result['status'] = 1;
            }
        }

        return response()->json($result);
    }

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


    public function updateAvatar(Request $request)
    {
        $avatar = $request->input('avatar');
        $username = $request->input('username');

        $result = DB::table('admins')
            ->where('username', $username)
            ->update(['avatar' => $avatar]);

        return response()->json([
            'result' => $result
        ]);
    }

    public function updateLastLogin(Request $request)
    {
        $adminId = $request->input('admin_id');

        $result = DB::table('admins')
            ->where('admin_id', $adminId)
            ->update(['last_login' => now()]);

        return response()->json([
            'result' => $result
        ]);
    }

    public function adminInfo(Request $request)
    {
        // Kiểm tra xem request có chứa trường 'username' không
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

}
