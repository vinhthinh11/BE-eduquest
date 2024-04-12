<?php

namespace App\Http\Controllers;

use App\Models\classes;
use App\Models\teacher;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;

class AdminTeacherController extends Controller
{
    public function getTeacher()
    {
        $data = teacher::get();
        if ($data->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No teacher found!',
            ], 400);
        }
        return response()->json([
            'data'    => $data,
        ]);
    }

    public function destroy(Request $request)
    {
        $teacher = teacher::find($request->teacher_id);

        if($teacher) {
            $class = classes::where('class_id', $request->teacher_id)->first();

            if($class) {
                return response()->json([
                    'status'    => 2,
                    'message'   => 'Giáo Viên đang đứng Lớp, bạn không thể xóa!'
                ]);
            } else {
                $teacher->delete();

                return response()->json([
                    'status'    => true,
                    'message'   => 'Đã xóa Giáo Viên thành công!'
                ]);
            }
        } else {
            return response()->json([
                'status'    => false,
                'message'   => 'Giáo Viên không tồn tại!'
            ]);
        }
    }


    public function update(Request $request)
    {
        $teacher = teacher::find($request->teacher_id);
        $data = $request->all();

        if ($teacher) {
            $teacher->update($data);

            return response()->json([
                'status'    => true,
                'message'   => 'Đã lấy được thông tin Giáo Viên thành công!',
            ]);
        } else {
            return response()->json([
                'status'    => false,
                'message'   => 'Giáo Viên không tồn tại trên hệ thống!',
            ]);
        }
    }

    public function edit(Request $request)
    {
        $teacher = teacher::find($request->teacher_id);
        $data = $request->only(['name', 'gender_id', 'birthday', 'password']);

        if (!$teacher) {
             return response()->json([
                'status'    => false,
                'message'   => 'Tài khoản không tồn tại!'
            ]);
        }
        // Kiểm tra admin muốn cập nhật mật khẩu cho giáo viên không
        else if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']); //  bcrypt password
        }

        $teacher->fill($data)->save();

        return response()->json([
            'status'    => true,
            'message'   => 'Cập Nhập thành công thông tin Giáo Viên!',
            'teacher'   => $teacher,
        ]);
    }

    public function create(Request $request)
    {
        $data = $request->all();
        teacher::create($data);

        return response()->json([
            'status'    => true,
            'message'   => 'Đã tạo mới Giáo Viên thành công!',
        ]);
    }

    public function createFileTeacher(Request $request)
    {
        $result = [];

        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->path();

            $reader = IOFactory::createReader('Xlsx');
            $spreadsheet = $reader->load($filePath);
            $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

            $count = 0;
            $errList = [];

            foreach ($sheetData as $key => $row) {
                if ($key < 4) {
                    continue;
                }
                if (empty($row['A'])) {
                    continue;
                }

                $name = $row['B'];
                $username = $row['C'];
                $email = $row['D'];
                $password = md5($row['E']);
                $birthday = $row['F'];
                $gender = ($row['G'] == 'Nam') ? 2 : (($row['G'] == 'Nữ') ? 3 : 1);

                $teacher = new teacher([
                    'name' => $name,
                    'username' => $username,
                    'email' => $email,
                    'password' => $password,
                    'birthday' => $birthday,
                    'gender_id' => $gender,
                    'last_login' => now(),
                ]);

                if ($teacher->saveQuietly()) {
                } else {
                    $errList[] = $row['A'];
                }
            }

            unlink($filePath);

            if (empty($errList)) {
                $result['status_value'] = "Thêm thành công " . $count . " Giáo Viên!";
                $result['status'] = 1;
            } else {
                $result['status_value'] = "Lỗi! Không thể thêm Giáo Viên có STT: " . implode(', ', $errList) . ', vui lòng xem lại.';
                $result['status'] = 0;
            }
        } else {
            $result['status_value'] = "Không tìm thấy tệp được tải lên!";
            $result['status'] = 0;
        }

        return response()->json($result);
    }

    public function search(Request $request)
    {
        $list = teacher::select('teachers.*')
            ->where('name', 'like', '%' . $request->key_search . '%')
            ->orWhere('username', 'like', '%' . $request->key_search . '%')
            ->get();

        return response()->json([
            'list'  => $list
        ]);
    }
    public function deleteCheckbox(Request $request)
    {
        $data = $request->all();
        $deletedTeachers = [];

        foreach ($data as $key => $value) {
            if (isset($value['check'])) {
                $teacherId = $value['teacher_id'];
                $teacher = teacher::find($teacherId);

                if ($teacher) {
                    $class = classes::where('teacher_id', $teacherId)->first();

                    if ($class) {
                        return response()->json([
                            'status' => false,
                            'message' => 'Không thể xóa giáo viên vì giáo viên đang đứng lớp!',
                        ]);
                    }

                    //Không đứng thì xóa thôi
                    $teacher->delete();
                    $deletedTeachers[] = $teacherId;
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'Giáo viên có teacher_id ' . $teacherId . ' không tồn tại!',
                    ]);
                }
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Xóa các giáo viên thành công!',
            'deleted_teachers' => $deletedTeachers,
        ]);
    }
}
