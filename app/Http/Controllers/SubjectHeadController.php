<?php

namespace App\Http\Controllers;

use App\Models\subject_head;
use Carbon\Carbon;
use Carbon\CarbonTimeZone;
use Illuminate\Http\Request;
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
        $data['id'] = $request->id;
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3|max:255',
            'gender_id' => 'required',
            'birthday' => 'nullable|date',
            'password' => 'required|min:6|max:20',
            'email' => 'nullable|email|unique:subject_head,email,'.$data['id'].',subject_head_id',
        ], [
            'name.required' => 'Vui lòng nhập tên!',
            'name.min' => 'Tên cần ít nhất 3 ký tự!',
            'name.max' => 'Tên dài nhất 255 ký tự!',
            'gender_id.required' => 'Vui lòng chon giới tính!',
            'birthday.date' => 'Ngày sinh chưa đúng định dạng!',
            'password.required' => 'Vui lòng nhập mật khẩu!',
            'password.min' => 'Vui nhap it nhat 6 ky tu!',
            'email.email' => 'Vui long nhap email hop le!',
            'email.unique' => 'Email da ton tai!',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        $me = subject_head::find($request->id);
        $me->update([
                    'name' => $request['name'],
                    'email' => $request['email'],
                    'gender_id' => $request['gender_id'],
                    'birthday' => $request['birthday'],
                    'password' => bcrypt($request['password']),
                    'last_login' => Carbon::now(CarbonTimeZone::createFromHourOffset(7 * 60))->timezone('Asia/Ho_Chi_Minh'),
                ]);
        return response()->json([
            'status' => true,
            'message' => "Cập nhập tài khoản cá nhân thành công!"
        ]);
    }

    public function updateAvatarProfile(Request $request)
    {
        $subject_head = subject_head::find($request->id);

        if (!$subject_head) {
            return response()->json([
                'status' => false,
                'message' => 'Trưởng bộ môn không tồn tại!',
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
            $subject_head->avatar = $path;
            $subject_head->save();

            return response()->json(['message' => 'Tải lên thành công', 'path' => $path], 200);
        } else {
            return response()->json(['message' => 'Không có tệp nào được tải lên'], 404);
        }
    }
}
