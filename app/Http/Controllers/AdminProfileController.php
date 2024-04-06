<?php

namespace App\Http\Controllers;

use App\Models\admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $username = $request->input('username');
        $name = $request->input('name');
        $email = $request->input('email');
        $password = md5($request->input('password'));
        $gender = $request->input('gender');
        $birthday = $request->input('birthday');

        $info = new admin();
        $result = $info->update_profiles($username, $name, $email, $password, $gender, $birthday);

        return response()->json([
            'result' => $result
        ]);
    }

    public function updateAvatar(Request $request)
    {
        $avatar = $request->input('avatar');
        $username = $request->input('username');

        $info = new admin();
        $result = $info->update_avatar($avatar, $username);

        return response()->json([
            'result' => $result
        ]);
    }

    public function updateLastLogin(Request $request)
    {
        $adminId = $request->input('admin_id');

        $info = new admin();
        $info->update_last_login($adminId);

        return response()->json([
            'message' => 'Cập nhập thành công lần đăng nhập cuối!'
        ]);
    }

    public function adminInfo(Request $request)
    {
        $username = $request->input('username');

        $info = new admin();
        $adminInfo = $info->get_admin_info($username);

        return response()->json([
            'adminInfo' => $adminInfo
        ]);
    }
}
