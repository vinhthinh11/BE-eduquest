<?php

namespace App\Http\Controllers;

use App\Models\classes;
use App\Models\student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        if (!$class) {
            return response()->json([
                'status'    => false,
                'message'   => 'Lớp không tồn tại trên hệ thống!',
            ]);
        }

        DB::beginTransaction();

        $students = student::where('class_id', $class->class_id)->get();

        foreach ($students as $student) {
            $student->class_id = 1; //cái này cần set lại db cho defaul class_id = 1 là lớp chứa những sinh viên bị xóa Lớp nhé ae!
            $student->save();
        }

        DB::table('chats')->where('class_id', $class->class_id)->delete();
        DB::table('student_notifications')->where('class_id', $class->class_id)->delete();
        $class->delete();

        DB::commit();

        return response()->json([
            'status'    => true,
            'message'   => 'Xóa Lớp thành công!',
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
                'message'   => 'Đã lấy được thông tin Lớp thành công!',
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

        if (!$class) {
            return response()->json([
                'status' => false,
                'message' => 'Lớp không tồn tại trên hệ thống!',
            ]);
        }

        $class->fill($request->all());
        $class->save();

        return response()->json([
            'status' => true,
            'message' => 'Sửa thông tin lớp thành công!',
        ]);
    }

    public function create(Request $request)
    {
        $data = $request->all();
        classes::create($data);

        return response()->json([
            'status'    => true,
            'message'   => 'Đã tạo mới Lớp thành công!',
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

    public function deleteCheckbox(Request $request)
    {
        $data = $request->all();
        $deletedClasses = [];

        foreach ($data as $key => $value) {
            if (isset($value['check'])) {
                $classId = $value['class_id'];
                $class = classes::find($classId);

                if ($class) {
                    $students = student::where('class_id', $class->class_id)->get();

                    foreach ($students as $student) {
                        if ($student->class_id !== null) {
                            $student->class_id = 1;
                            $student->save();
                        }
                    }

                    DB::table('chats')->where('class_id', $class->class_id)->delete();
                    DB::table('student_notifications')->where('class_id', $class->class_id)->delete();

                    $class->delete();
                    $deletedClasses[] = $classId;
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'Lớp có class_id ' . $classId . ' không tồn tại!',
                    ]);
                }
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Xóa các lớp thành công!',
            'deleted_classes' => $deletedClasses,
        ]);
    }
}
