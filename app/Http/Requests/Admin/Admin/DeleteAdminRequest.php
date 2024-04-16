<?php

namespace App\Http\Requests\Admin\Admin;


use Illuminate\Foundation\Http\FormRequest;

class DeleteAdminRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

   public function rules()
    {
        return [
            'admin_id' => 'required|exists:admins,admin_id'
        ];
    }

    public function messages()
    {
        return [
            'admin_id.*' => 'Admin không tồn tại!',
        ];
    }
}
