<?php

namespace App\Http\Requests\Admin\Question;

use Illuminate\Foundation\Http\FormRequest;

class DeleteQuestionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

   public function rules()
    {
        return [
            'question_id' => 'required|exists:questions,question_id'
        ];
    }

    public function messages()
    {
        return [
            'question_id.*' => 'Câu hỏi không tồn tại!',
        ];
    }
}
