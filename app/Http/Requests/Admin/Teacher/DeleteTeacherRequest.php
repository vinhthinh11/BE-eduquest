<?php

namespace App\Http\Requests\Admin\Teacher;



use Illuminate\Foundation\Http\FormRequest;

class DeleteTeacherRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

   public function rules()
    {
        return [
            'teacher_id' => 'required|exists:teachers,teacher_id'
        ];
    }

    public function messages()
    {
        return [
            'teacher_id.*' => 'Giáo Viên không tồn tại!',
        ];
    }
}
