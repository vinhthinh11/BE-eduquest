<?php

namespace App\Http\Requests\Admin\Classes;


use Illuminate\Foundation\Http\FormRequest;

class DeleteClassRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

   public function rules()
    {
        return [
            'class_id' => 'required|exists:classes,class_id'
        ];
    }

    public function messages()
    {
        return [
            'class_id.*' => 'Lớp không tồn tại!',
        ];
    }
}
