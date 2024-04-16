<?php

namespace App\Http\Requests\Admin\Subject;

use Illuminate\Foundation\Http\FormRequest;

class Update_CreateSubjectRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'subject_detail'      => 'required|string|max:20',
        ];
    }

    public function messages()
    {
        return [
            'subject_detail.required'     => 'Môn học không được để trống!',
            'subject_detail.max'       => 'Tên Môn học tối đa 20 kí tự!',
            'subject_detail.string'       => 'Tên Môn học phải là dạng chuỗi!',
        ];
    }
}
