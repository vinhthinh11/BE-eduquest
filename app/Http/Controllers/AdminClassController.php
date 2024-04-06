<?php

namespace App\Http\Controllers;

use App\Models\classes;
use Illuminate\Http\Request;

class AdminClassController extends Controller
{
    public function getClasses()
    {
        $data = classes::get();

        return response()->json([
            'data'    => $data
        ]);
    }

    public function destroy(Request $request)
    {
        $class = classes::find($request->class_id);

        if ($class) {
            $class->delete();
            return response()->json([
                'status'    => true,
                'message'   => 'Xóa Lớp thành công!',
            ]);
        } else
            return response()->json([
                'status'    => false,
                'message'   => 'Lớp không tồn tại trên hệ thống!',
            ]);
    }

    public function update(Request $request)
    {
        $class = classes::find($request->class_id);
        $data = $request->all();

        if ($class) {
            $class->update($data);

            return response()->json([
                'status'    => true,
                'message'   => 'Cập nhật Lớp thành công!',
            ]);
        } else {
            return response()->json([
                'status'    => false,
                'message'   => 'Lớp không tồn tại trên hệ thống!',
            ]);
        }
    }

    public function edit(Request $request)
    {
        $class = classes::find($request->class_id);

        if($class) {
            return response()->json([
                'status'    => true,
                'message'   => 'Cập Nhập thành công thông tin Lớp!',
                'class$class'    => $class,
            ]);
        } else {
            return response()->json([
                'status'    => false,
                'message'   => 'Cập nhập Lớp thất bại!'
            ]);
        }
    }

    public function create(Request $request)
    {
        $data = $request->all();
        classes::create($data);

        return response()->json([
            'status'    => true,
            'message'   => 'Đã tạo mới thành công!',
        ]);
    }

    public function search(Request $request)
    {
        $list = classes::join('teachers' , 'teachers.teacher_id' , 'classes.teacher_id')
                        ->select('classes.*', 'teachers.name')
                        ->where('class_name', 'like', '%' . $request->key_search . '%')
                        ->orWhere('teachers.name', 'like', '%' . $request->key_search . '%')
                        ->get();

        return response()->json([
            'list'  => $list
        ]);
    }
}
