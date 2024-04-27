<?php

namespace App\Http\Controllers;

use App\Models\subject_head;
use Carbon\Carbon;
use Carbon\CarbonTimeZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;


class SubjectHeadController extends Controller
{
    public function getInfo($username)
    {
        //không chạy đc sql thì vào xóa 's' ở sau tên table của câu lệnh nha, t chưa check cái ni, cảm ơn vì đã check giúp.
        $subject_head = subject_head::select('subject_heads.subject_head_id', 'subject_heads.username', 'subject_heads.avatar', 'subject_heads.email', 'subject_heads.name', 'subject_heads.last_login', 'subject_heads.birthday', 'permissions.permission_detail', 'genders.gender_detail', 'genders.gender_id')
            ->join('permissions', 'subject_heads.permission', '=', 'permissions.permission')
            ->join('genders', 'subject_heads.gender_id', '=', 'genders.gender_id')
            ->where('subject_heads.username', '=', $username)
            ->first();
        if ($subject_head) {
            //đẩy view ở đây nha!!
            //return view('subject_head.info', ['subject_head' => $subject_head]);
            return response()->json(['subject_head' => $subject_head], 200);
        }
            return response()->json(['message' => 'Trưởng bộ môn không tồn tại!'], 404);
    }
    public function updateProfile(Request $request)
    {
        $me = $request->user('subject_head');
        // $validator = Validator::make($request->all(), [
        //     'name' => 'sometimes|min:3|max:255',
        //     'gender_id' => 'sometimes|integer',
        //     'birthday' => 'sometimes|date',
        //     'password' => 'sometimes|min:6|max:20',
        //     'email' => 'sometimes|email|unique:admins,email',
        //     'avatar' => 'somtimes|mimes:jpeg,png,jpg,gif,svg|max:2048',
        // ]);

        // if ($validator->fails()) {
        //     return response()->json([
        //         'status' => false,
        //         'errors' => $validator->errors(),
        //     ], 422);
        // }

        if ($request->hasFile('avatar')) {
            if ($me->avatar != "avatar-default.jpg") {
                Storage::delete('public/' . str_replace('/storage/', '', $me->avatar));
            }
            $image = $request->file('avatar');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('images',  $imageName, 'public');
            $data['avatar'] = '/storage/' . $imagePath;
        }

        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        }

        $me->update($data);

        return response()->json([
            'status' => true,
            'message' => "Cập nhập tài khoản cá nhân thành công!"
        ]);
    }

    public function updateAvatarProfile(Request $request)
    {
        $user = $request->user('subject_head');

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

        if ($request->hasFile('avatar')) {
            $image = $request->file('avatar');
            $path = $image->store('images/sunject_head');

        if ($user->avatar) {
            Storage::delete($user->avatar);
        }

            $user->avatar = $path;
            $user->save();

            return response()->json(['message' => 'Tải lên thành công', 'path' => $path], 200);
        }
            return response()->json(['message' => 'Không có tệp nào được tải lên'], 404);
    }
}
