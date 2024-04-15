<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            //'username' => 'required|string|exists:admins,username',
            'email'    => 'required|email',
            'password' => 'required|string|max:20|min:6',
        ];
    }

    public function messages()
    {
        return [
            // 'username.required' => 'Tên đăng nhập là bắt buộc!',
            // 'username.exists'   => 'Tên đăng nhập không tồn tại!',
            'email.required'    => 'Email là bắt buộc!',
            'email.email'       => 'Email phải là định dạng hợp lệ!',
            'password.required' => 'Mật khẩu là bắt buộc!',
            'password.min'      => 'Mật khẩu tối thiểu 6 kí tự!',
            'password.max'      => 'Mật khẩu tối đa 20 kí tự!',
        ];
    }
}
