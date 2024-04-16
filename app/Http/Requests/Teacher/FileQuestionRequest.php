<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;

class FileQuestionRequest extends FormRequest
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
            'file' => 'required|mimes:xlsx',
            'subject_id' => 'required|integer|exists:subjects,subject_id',
        ];
    }

    public function messages()
    {
        return [
            'file.required' => 'Vui lòng chọn một tập tin.',
            'file.mimes' => 'Tệp phải là tệp XLSX hợp lệ.',
            'subject_id.required' => 'Vui lòng chọn môn học.',
            'subject_id.exists' => 'Mã môn Học không tồn tại.',
        ];
    }
}
