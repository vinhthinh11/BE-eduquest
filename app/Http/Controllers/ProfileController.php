<?php

namespace App\Http\Controllers;

use App\Rules\EmailExistsInMultipleTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;


class ProfileController extends Controller
{
    public function updateProfile(Request $request)
    {



        $validator = Validator::make($request->all(), [
            'name' => 'nullable|min:3|max:255',
            'gender_id' => 'nullable|integer',
            'birthday' => 'nullable|date',
            'password' => 'nullable|min:6|max:20',
            'email' => 'nullable|email', new EmailExistsInMultipleTables,
            'avatar' => 'nullable|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        $user_type = ['admins', 'subject_heads', 'teachers', 'students'];
        foreach ($user_type as $type) {
            $user = $request->user($type);
            if ($user) {
                break;
            }
        }
        if(!$user){
            return response()->json([
                'status' => false,
                'message' => 'User not found',
            ], 400);
        }

        $data = $request->only(['name', 'gender_id', 'birthday', 'email', 'permission']);

        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        }

        if ($request->hasFile('avatar')) {
            if ($user->avatar != "avatar-default.jpg") {
                Storage::delete('public/' . str_replace('/storage/', '', $user->avatar));
            }
            $image = $request->file('avatar');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('images', $imageName, 'public');
            $data['avatar'] = '/storage/' . $imagePath;
        }

        $user->update($data);

        return response()->json([
            'message' => 'Cập nhật thông tin cá nhân thành công!',
            'data' => $user
        ], 200);
    }
}
