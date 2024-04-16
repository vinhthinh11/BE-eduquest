<?php

namespace App\Http\Requests\Admin\SubjectHead;

use Illuminate\Foundation\Http\FormRequest;

class DuyetDeThiRequest extends FormRequest
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

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'test_code' => 'required|string|unique:tests,test_code',
        ];
    }
    public function messages()
    {
        return [
            'test_code.required' => 'Trường mã đề thi là bắt buộc.',
            'test_code.string' => 'Mã đề thi phải là một chuỗi.',
            'test_code.unique' => 'Mã đề thi đã tồn tại trong hệ thống.',
        ];
    }
}
