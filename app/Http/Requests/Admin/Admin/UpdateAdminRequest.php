<?php

namespace App\Http\Requests\Admin\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;

class UpdateAdminRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'admin_id' => 'required|exists:admins,admin_id',
            'name' => 'required|string|min:6|max:50',
            'gender_id' => 'required|integer',
            'birthday' => 'nullable|date',
            'password' => 'nullable|string|min:6|max:20',
        ];
    }

    public function messages()
    {
        return [
            'admin_id.required' => 'Admin không được để trống!',
            'admin_id.exists' => 'Admin không tồn tại!',
            'name.min' => 'Tên Admin tối thiểu 6 kí tự!',
            'name.required' => 'Tên Admin không được để trống!',
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
