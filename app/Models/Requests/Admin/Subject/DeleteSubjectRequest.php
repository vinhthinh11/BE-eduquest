<?php

namespace App\Http\Requests\Admin\Subject;

use Illuminate\Foundation\Http\FormRequest;

class DeleteSubjectRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

   public function rules()
    {
        return [
            'subject_id' => 'required|exists:subjects,subject_id'
        ];
    }

    public function messages()
    {
        return [
            'subject_id.*' => 'Môn học không tồn tại!',
        ];
    }
}
