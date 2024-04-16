<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required',
            'gender_id' => 'required',
            'birthday' => 'required',
            'password' => 'required',
            'email' => 'required|email',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Vui lòng nhập tên!',
            'gender_id.required' => 'Vui lòng chon giới tính!',
            'birthday.required' => 'Vui lòng nhập ngày sinh!',
            'password.required' => 'Vui lòng nhập mật khẩu!',
            'email.required' => 'Vui lòng nhập email!',
            'email.email' => 'Email khong hop le!',
        ];
    }
}
