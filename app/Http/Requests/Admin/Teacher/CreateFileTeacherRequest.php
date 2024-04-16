<?php

namespace App\Http\Requests\Admin\Teacher;

use Illuminate\Foundation\Http\FormRequest;

class CreateFileTeacherRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|min:6|max:50',
            'username' => 'required|string|min:6|max:50|unique:teachers,username',
            'email' => 'nullable|email|unique:teachers,email',
            'password' => 'required|string|min:6|max:20',
            'birthday' => 'nullable|date',
            'gender' => 'required|string|in:Nam,Nữ,Khác',
            'permission' => 'nullable|string|unique:permissions,permission',
        ];
    }

    public function messages()
    {
        return [
            'name.string' => 'Tên Giáo Viên phải là chuỗi!',
            'name.required' => 'Tên Giáo Viên không được để trống!',
            'username.required' => 'Username không được để trống!',
            'username.unique' => 'Username đã tồn tại!',
            'password.required' => 'Password không được để trống!',
            'password.min' => 'Password tối thiểu 6 kí tự!',
            'email.email' => 'Email không đúng định dạng!',
            'email.unique' => 'Email đã được sử dụng!',
            'birthday.date' => 'Ngày Sinh phải là một ngày hợp lệ!',
            'permission.unique' => 'Giá trị cho Permission đã tồn tại!',
        ];
    }
}
