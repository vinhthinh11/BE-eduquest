<?php

namespace App\Http\Requests\Admin\Classes;

use Illuminate\Foundation\Http\FormRequest;

class Update_CreateClassRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'grade_id'      => 'required|exists:grades,grade_id',
            'class_name'    => 'required|string',
            'teacher_id'    => 'required|exists:teachers,teacher_id',
        ];
    }

    public function messages()
    {
        return [
            'grade_id.required'     => 'Khối không được để trống!',
            'grade_id.exists'       => 'Khối không tồn tại trong cơ sở dữ liệu!',
            'class_name.required'   => 'Tên Lớp không được để trống!',
            'teacher_id.required'   => 'Giáo viên không được để trống!',
            'teacher_id.exists'     => 'Giáo viên không tồn tại trong cơ sở dữ liệu!',
        ];
    }
}
