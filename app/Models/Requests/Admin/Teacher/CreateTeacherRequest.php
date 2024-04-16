<?php

namespace App\Http\Requests\Admin\Teacher;


use Illuminate\Foundation\Http\FormRequest;

class CreateTeacherRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name'          => 'required|string|min:6|max:50',
            'username'      => 'required|string|min:6|max:50|unique:teachers,username',
            'gender_id'     => 'required|integer',
            'password'      => 'required|string|min:6|max:20',
            'email'         => 'nullable|email|unique:teachers,email',
            'permission'    => 'nullable|unique:permissions,permission',
            'birthday'      => 'nullable|date',
        ];
    }

    public function messages()
    {
        return [
            'name.min'           => 'Tên Giáo Viên tối thiểu 6 kí tự!',
            'name.required'         => 'Tên Giáo Viên không được để trống!',
            'username.required'     => 'Username không được để trống!',
            'username.unique'       => 'Username đã tồn tại!',
            'password.required'     => 'Password không được để trống!',
            'password.min'          => 'Password tối thiểu 6 kí tự!',
            'email.email'           => 'Email không đúng định dạng!',
            'email.unique'          => 'Email đã được sử dụng!',
            'birthday.date'         => 'Ngày Sinh phải là một ngày hợp lệ!',
            'permission.unique'     => 'Giá trị cho Permission đã tồn tại!',
        ];
    }
}
