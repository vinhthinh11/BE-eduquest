<?php

namespace App\Http\Requests\Admin\Question;

use Illuminate\Foundation\Http\FormRequest;

class CreateQuestionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'subject_id'        => 'required|integer|exists:subjects,subject_id',
            'question_content'  => 'required|string',
            'grade_id'          => 'required|integer|exists:grades,grade_id',
            'level_id'          => 'required|integer|exists:levels,level_id',
            'unit'              => 'required|string',
            'answer_a'          => 'required|string',
            'answer_b'          => 'required|string',
            'answer_c'          => 'required|string',
            'answer_d'          => 'required|string',
            'correct_answer'    => 'required|in:A,B,C,D',
            'status_id'         => 'required|integer|in:1,2,3',
            'suggest'           => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'subject_id.required'           => 'Mã môn học không được để trống!',
            'subject_id.exists'             => 'Mã môn học không tồn tại!',
            'question_content.required'     => 'Nội dung câu hỏi không được để trống!',
            'grade_id.required'             => 'Mã khối học không được để trống!',
            'grade_id.exists'               => 'Mã khối học không tồn tại!',
            'level_id.required'             => 'Mã cấp độ không được để trống!',
            'level_id.exists'               => 'Mã cấp độ không tồn tại!',
            'unit.required'                 => 'Đơn vị không được để trống!',
            'answer_a.required'             => 'Câu trả lời A không được để trống!',
            'answer_b.required'             => 'Câu trả lời B không được để trống!',
            'answer_c.required'             => 'Câu trả lời C không được để trống!',
            'answer_d.required'             => 'Câu trả lời D không được để trống!',
            'correct_answer.required'       => 'Câu trả lời đúng không được để trống!',
            'correct_answer.in'             => 'Câu trả lời đúng phải là A, B, C hoặc D!',
            'status_id.required'            => 'Trạng thái không được để trống!',
            'status_id.in'                  => 'Trạng thái không hợp lệ!',
        ];
    }
}
