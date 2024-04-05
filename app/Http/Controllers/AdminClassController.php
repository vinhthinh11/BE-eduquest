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
                'message'   => 'Hệ thống gặp sự cố dữ liệu!',
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
                'message'   => 'Hệ thống gặp sự cố dữ liệu!',
            ]);
        }
    }

    public function create(Request $request)
    {
        $result = [];

        $grade_id = $request->input('grade_id');
        $class_name = $request->input('class_name');
        $teacher_id = $request->input('teacher_id');

        $class = new classes([
            'grade_id' => $grade_id,
            'class_name' => $class_name,
            'teacher_id' => $teacher_id
        ]);

        if ($class->save()) {
            $result = $class->toArray();
            $result['status_value'] = "Thêm lớp học thành công!";
            $result['status'] = 1;
        } else {
            $result['status_value'] = "Lỗi! Không thể thêm lớp học!";
            $result['status'] = 0;
        }

        return response()->json([
            'result' => $result,
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
