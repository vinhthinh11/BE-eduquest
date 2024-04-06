<?php

namespace App\Http\Controllers;

use App\Models\teacher;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Http\Request;

class AdminTeacherController extends Controller
{
    public function getTeacher()
    {
        $data = teacher::get();

        return response()->json([
            'data'    => $data,
        ]);
    }

    public function destroy(Request $request)
    {
        $teacher = teacher::find($request->teacher_id);

        if ($teacher) {
            $teacher->delete();
            return response()->json([
                'status'    => true,
                'message'   => 'Xóa Giáo Viên thành công!',
            ]);
        } else
            return response()->json([
                'status'    => false,
                'message'   => 'Giáo Viên không tồn tại trên hệ thống!',
            ]);
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

        if($teacher) {
            return response()->json([
                'status'    => true,
                'message'   => 'Cập Nhập thành công thông tin Giáo Viên!',
                'teacher'    => $teacher,
            ]);
        }
        else
            return response()->json([
                'status'    => false,
                'message'   => 'Cập nhập Giáo Viên thất bại!'
            ]);
    }

    public function create(Request $request)
    {
        $data = $request->all();
        teacher::create($data);

        return response()->json([
            'status'    => true,
            'message'   => 'Đã tạo mới thành công!',
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
                }if (empty($row['A'])) {
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
                    $count++;
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
}
