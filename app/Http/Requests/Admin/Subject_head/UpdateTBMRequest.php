<?php

namespace App\Http\Requests\Admin\Subject_head;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;

class UpdateTBMRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'subject_head_id' => 'required|exists:subject_head,subject_head_id',
            'name' => 'required|string|min:6|max:50',
            'gender_id' => 'required|integer',
            'birthday' => 'nullable|date',
            'password' => 'nullable|string|min:6|max:20',
        ];
    }

    public function messages()
    {
        return [
            'subject_head_id.required' => 'Trưởng bộ môn không được để trống!',
            'student_id.exists' => 'Trưởng bộ môn không tồn tại!',
            'name.min' => 'Tên Trưởng bộ môn tối thiểu 6 kí tự!',
            'name.required' => 'Tên Trưởng bộ môn không được để trống!',
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
