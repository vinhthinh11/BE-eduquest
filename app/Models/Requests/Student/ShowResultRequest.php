<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class ShowResultRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Có thể bạn muốn thêm logic xác thực ở đây nếu cần thiết
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'student_id' => 'required|exists:students,student_id',
            'test_code' => 'required|exists:tests,test_code',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'student_id.required' => 'Trường student_id là bắt buộc.',
            'student_id.exists' => 'Học sinh không tồn tại.',
            'test_code.required' => 'Trường test_code là bắt buộc.',
            'test_code.exists' => 'Bài thi không tồn tại.',
        ];
    }
}
