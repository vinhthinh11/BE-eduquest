<?php

namespace App\Http\Requests\Admin\Teacher;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;

class UpdateTeacherRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'teacher_id' => 'required|exists:teachers,teacher_id',
            'name' => 'required|string|min:6|max:50',
            'gender_id' => 'required|integer',
            'birthday' => 'nullable|date',
            'password' => 'nullable|string|min:6|max:20',
        ];
    }

    public function messages()
    {
        return [
            'teacher_id.required' => 'Giáo Viên không được để trống!',
            'teacher_id.exists' => 'Giáo Viên không tồn tại!',
            'name.min' => 'Tên Giáo Viên tối thiểu 6 kí tự!',
            'name.required' => 'Tên Giáo Viên không được để trống!',
            'gender_id.required' => 'Giới tính không được để trống!',
            'birthday.date' => 'Ngày Sinh phải là một ngày hợp lệ!',
            'password.min' => 'Mật khẩu tối thiểu 6 kí tự!',
            'password.max' => 'Mật khẩu không được quá 20 kí tự!',
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->has('password')) {
            $this->merge([
                'password' => Hash::make($this->password),
            ]);
        }
    }
}
