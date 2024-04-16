<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuestionRequest extends FormRequest
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
            'subject_id' => 'required|exists:subjects,subject_id',
            'level_id' => 'required|exists:levels,level_id',
            'grade_id' => 'required|exists:grades,grade_id',
            'teacher_id' => 'required|exists:teachers,teacher_id',
            'question_content' => 'required|string|min:3|max:255',
            'unit' => 'required|string|min:1|max:255',
            'answer_a' => 'required|string|min:1|max:255',
            'answer_b' => 'required|string|min:1|max:255',
            'answer_c' => 'required|string|min:1|max:255',
            'answer_d' => 'required|string|min:1|max:255',
            'correct_answer' => 'required|in:a,b,c,d',
            'suggest' => 'required|string|min:1|max:255',
        ];
    }
    public function messages()
    {
        return [
            'subject_id.required' => 'Vui lòng chọn môn học!',
            'question_content.required' => 'Vui lòng nhập nội dung câu hỏi!',
            'level_id.required' => 'Vui lòng chọn cấp độ!',
            'grade_id.required' => 'Vui lòng chọn lớp!',
            'unit.required' => 'Vui lòng nhập đơn vị!',
            'answer_a.required' => 'Vui lòng nhập đáp án A!',
            'answer_b.required' => 'Vui lòng nhập đáp án B!',
            'answer_c.required' => 'Vui lòng nhập đáp án C!',
            'answer_d.required' => 'Vui lòng nhập đáp án D!',
            'correct_answer.required' => 'Vui lòng chọn đáp án đúng!',
            'suggest.required' => 'Vui lòng nhập gợi ý!',
            'teacher_id.required' => 'Vui lòng chọn giáo viên!',
        ];
    }
}
