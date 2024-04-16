<?php

namespace App\Http\Requests\Admin\Subject_head;

use Illuminate\Foundation\Http\FormRequest;

class DeleteTBMRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

   public function rules()
    {
        return [
            'subject_head_id' => 'required|exists:subject_head,subject_head_id'
        ];
    }

    public function messages()
    {
        return [
            'subject_head_id.*' => 'Trưởng bộ môn không tồn tại!',
        ];
    }
}
