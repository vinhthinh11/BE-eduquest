<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;

class ExportScoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'test_code' => 'required|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'test_code.required' => 'Mã bài thi không được để trống!',
            'test_code.max'      => 'Mã bài thi không quá 255 kí tự!',
        ];
    }
}
