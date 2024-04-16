<?php

namespace App\Http\Requests\Admin\Student;

use Illuminate\Foundation\Http\FormRequest;

class DeleteStudentRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

   public function rules()
    {
        return [
            'student_id' => 'required|exists:students,student_id'
        ];
    }

    public function messages()
    {
        return [
            'student_id.*' => 'Học Sinh không tồn tại!',
        ];
    }
}
