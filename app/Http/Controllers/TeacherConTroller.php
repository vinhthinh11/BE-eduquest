<?php

namespace App\Http\Controllers;

use App\Models\classes;
use App\Models\questions;
use App\Models\scores;
use App\Models\student;
use App\Models\students;
use App\Models\teacher;
use Carbon\Carbon;
use Carbon\CarbonTimeZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Illuminate\Support\Facades\Validator;

class TeacherConTroller extends Controller
{
    public function getInfo($username)
    {
        $teacher = teacher::select('teachers.teacher_id', 'teachers.username', 'teachers.avatar', 'teachers.email', 'teachers.name', 'teachers.last_login', 'teachers.birthday', 'permissions.permission_detail', 'genders.gender_detail', 'genders.gender_id')
            ->join('permissions', 'teachers.permission', '=', 'permissions.permission')
            ->join('genders', 'teachers.gender_id', '=', 'genders.gender_id')
            ->where('teachers.username', '=', $username)
            ->first();
        if ($teacher) {
            //đẩy view ở đây nha!!
            //return view('teacher.info', ['teacher' => $teacher]);
            return response()->json(['teacher' => $teacher], 200);
        }
            return response()->json(['message' => 'Giáo viên không tồn tại!'], 404);
    }
    public function updateProfile(Request $request)
    {
        $data['id'] = $request->id;
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3|max:255',
            'gender_id' => 'required',
            'birthday' => 'nullable|date',
            'password' => 'required|min:6|max:20',
            'email' => 'nullable|email|unique:teachers,email,'.$data['id'].',teacher_id',
        ], [
            'name.required' => 'Vui lòng nhập tên!',
            'name.min' => 'Tên cần ít nhất 3 ký tự!',
            'name.max' => 'Tên dài nhất 255 ký tự!',
            'gender_id.required' => 'Vui lòng chon giới tính!',
            'birthday.date' => 'Ngày sinh chưa đúng định dạng!',
            'password.required' => 'Vui lòng nhập mật khẩu!',
            'password.min' => 'Vui nhap it nhat 6 ky tu!',
            'email.email' => 'Vui long nhap email hop le!',
            'email.unique' => 'Email da ton tai!',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        $me = teacher::find($request->id);
        $me->update([
                    'name' => $request['name'],
                    'email' => $request['email'],
                    'gender_id' => $request['gender_id'],
                    'birthday' => $request['birthday'],
                    'password' => bcrypt($request['password']),
                    'last_login' => Carbon::now(CarbonTimeZone::createFromHourOffset(7 * 60))->timezone('Asia/Ho_Chi_Minh'),
                ]);
        return response()->json([
            'status' => true,
            'message' => "Cập nhập tài khoản cá nhân thành công!"
        ]);
    }

    public function updateAvatarProfile(Request $request)
    {
        $teacher = teacher::find($request->id);

        if (!$teacher) {
            return response()->json([
                'status' => false,
                'message' => 'Giáo viên không tồn tại!',
            ], 404);
        }

        if ($request->hasFile('avatar')) {
            $validator = Validator::make($request->all(), [
                'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ], [
                'avatar.required' => 'Vui lòng chọn hình ảnh đại diện',
                'avatar.image' => 'Vui lòng chọn hình ảnh đại diện',
                'avatar.mimes' => 'Vui lòng chọn hình ảnh đúng định dạng (jpeg, png, jpg, gif, svg)',
                'avatar.max' => 'Kích thước hình ảnh không được vượt quá 2048KB',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $image = $request->file('avatar');
            $path = $image->store('images');
            $teacher->avatar = $path;
            $teacher->save();

            return response()->json(['message' => 'Tải lên thành công', 'path' => $path], 200);
        } else {
            return response()->json(['message' => 'Không có tệp nào được tải lên'], 404);
        }
    }
    //show điểm
    public function listScore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'ID học sinh không tồn tại!',
                'errors'    => $validator->errors(),
            ], 422);
        }
        $student_id = $request->input('student_id', '1');
        $name = $request->input('name');
        $scoreData  = scores::where('student_id', $student_id)
            ->get();

        return response()->json([
            'scoreData' => $scoreData,
            'success'   => true,
            'message'   => 'Show điểm thành công của học sinh'  . $name . " thành công!",
        ], 200);
    }

    //xuất file điểm
    public function exportScore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'test_code' => 'required|string|max:255',
        ], [
            'test_code.required' => 'Mã bài thi không được để trống!',
            'test_code.max'      => 'Mã bài thi không quá 255 kí tự!',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        $test_code = $request->input('test_code', '');

        $sql = "SELECT * FROM `scores` INNER JOIN students ON scores.student_id = students.student_id
            INNER JOIN classes ON students.class_id = classes.class_id
            WHERE test_code = ?";

        $scores = DB::select($sql, [$test_code]);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Danh Sách Điểm Bài Thi ' . $test_code);
        $sheet->setCellValue('A3', 'STT');
        $sheet->setCellValue('B3', 'Tên');
        $sheet->setCellValue('C3', 'Tài Khoản');
        $sheet->setCellValue('D3', 'Lớp');
        $sheet->setCellValue('E3', 'Điểm');

        //add data
        foreach ($scores as $key => $score) {
            $row = $key + 4;
            $sheet->setCellValue('A' . $row, $key + 1);
            $sheet->setCellValue('B' . $row, $score->name);
            $sheet->setCellValue('C' . $row, $score->username);
            $sheet->setCellValue('D' . $row, $score->class_name);
            $sheet->setCellValue('E' . $row, $score->score_number);
        }

        // signature
        $lastRow = count($scores) + 5;
        $sheet->setCellValue('B' . $lastRow, 'Chữ kí giám thị 1');
        $sheet->setCellValue('E' . $lastRow, 'Chữ kí giám thị 2');

        // export to excel
        $writer = new Xlsx($spreadsheet);
        $filename = 'danh-sach-diem-' . $test_code . '.xlsx';
        $tempFilePath = tempnam(sys_get_temp_dir(), 'export_score_');
        $writer->save($tempFilePath);

        return response()->download($tempFilePath, $filename)->deleteFileAfterSend(true);
    }

    public function addQuestion(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'grade_id' => 'required|integer',
            'unit' => 'required|string',
            'level_id' => 'required|integer',
            'question_content' => 'required|string',
            'answer_a' => 'required|string',
            'answer_b' => 'required|string',
            'answer_c' => 'required|string',
            'answer_d' => 'required|string',
            'correct_answer' => 'required|string',
            'question_id' => 'nullable|integer',
            'subject_id' => 'required|integer',
            'teacher_id' => 'required|integer',
            'status_id' => 'required|integer',
            'suggest' => 'nullable|string',
        ], [
            'grade_id.required' => 'Vui lòng chọn mức độ học.',
            'unit.required' => 'Vui lòng nhập đơn vị.',
            'level_id.required' => 'Vui lòng chọn cấp độ.',
            'question_content.required' => 'Vui lòng nhập nội dung câu hỏi.',
            'answer_a.required' => 'Vui lòng nhập đáp án A.',
            'answer_b.required' => 'Vui lòng nhập đáp án B.',
            'answer_c.required' => 'Vui lòng nhập đáp án C.',
            'answer_d.required' => 'Vui lòng nhập đáp án D.',
            'correct_answer.required' => 'Vui lòng chọn đáp án đúng.',
            'subject_id.required' => 'Vui lòng chọn môn học.',
            'teacher_id.required' => 'Vui lòng chọn giáo viên.',
            'status_id.required' => 'Vui lòng chọn trạng thái.',
            'suggest.string' => 'Gợi ý phải là chuỗi.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        $data = $request->all();

        try {
            DB::beginTransaction();

            $question = questions::create($data);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Thêm câu hỏi thành công!'
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'status' => false,
                'message' => 'Thêm câu hỏi thất bại!',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function addFileQuestion(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subject_id'        => 'required|integer|exists:subjects,subject_id',
            'question_content'  => 'required|string',
            'level_id'          => 'required|integer|exists:levels,level_id',
            'answer_a'          => 'required|string',
            'answer_b'          => 'required|string',
            'answer_c'          => 'required|string',
            'answer_d'          => 'required|string',
            'correct_answer'    => 'required|string|in:A,B,C,D',
            'grade_id'          => 'required|integer|exists:grades,grade_id',
            'unit'              => 'required|string',
            'suggest'           => 'nullable|string',
            'status_id'         => 'required|integer|in:1,2,3',
            'teacher_id'        => 'nullable|integer|exists:teachers,teacher_id',
            'file' => 'required|file|mimes:xlsx',
        ], [
            'subject_id.required'          => 'Mã môn học không được để trống!',
            'subject_id.exists'            => 'Mã môn học không tồn tại!',
            'question_content.required'    => 'Nội dung câu hỏi không được để trống!',
            'level_id.required'            => 'Mã cấp độ không được để trống!',
            'level_id.exists'              => 'Mã cấp độ không tồn tại!',
            'answer_a.required'            => 'Câu trả lời A không được để trống!',
            'answer_b.required'            => 'Câu trả lời B không được để trống!',
            'answer_c.required'            => 'Câu trả lời C không được để trống!',
            'answer_d.required'            => 'Câu trả lời D không được để trống!',
            'correct_answer.required'      => 'Câu trả lời đúng không được để trống!',
            'correct_answer.in'            => 'Câu trả lời đúng phải là A, B, C hoặc D!',
            'grade_id.required'            => 'Mã khối học không được để trống!',
            'grade_id.integer'             => 'Mã khối học phải là số nguyên!',
            'grade_id.exists'              => 'Mã khối học không tồn tại!',
            'unit.required'                => 'Đơn vị không được để trống!',
            'suggest.string'                => 'Gợi ý phải là chuỗi!',
            'status_id.required'           => 'Trạng thái không được để trống!',
            'status_id.in'                 => 'Trạng thái không hợp lệ!',
            'teacher_id.exists'            => 'Mã giáo viên không tồn tại!',
            'file.required'                => 'Vui lòng chọn tệp để tiếp tục!',
            'file.mimes'                   => 'File phải là xlsx!',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        $inputFileType = 'Xlsx';
        $result = array();
        $shuffle = array();
        $subject_id = $request->input('subject_id');

        $reader = IOFactory::createReader($inputFileType);
        $spreadsheet = $reader->load($request->file('file')->getRealPath());
        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

        $count = 0;
        $err_list = '';
        for ($i = 4; $i < count($sheetData); $i++) {
            if (empty($sheetData[$i]['A'])) {
                continue;
            }
            $stt = $sheetData[$i]['A'];
            $question_content = $sheetData[$i]['B'];
            $level_id = $sheetData[$i]['C'];
            $answer_a = $sheetData[$i]['D'];
            $answer_b = $sheetData[$i]['E'];
            $answer_c = $sheetData[$i]['F'];
            $answer_d = $sheetData[$i]['G'];
            $correct_answer = $sheetData[$i]['H'];
            $grade_id = $sheetData[$i]['I'];
            $unit = $sheetData[$i]['J'];
            $suggest = $sheetData[$i]['K'];
            $teacher_id = null;
            if (empty($question_content) || empty($grade_id) || empty($level_id) || empty($unit) || empty($answer_a) || empty($answer_b) || empty($answer_c) || empty($answer_d) || empty($correct_answer)) {
                continue;
            }

            switch ($correct_answer) {
                case "A":
                    $answer = $answer_a;
                    break;
                case "B":
                    $answer = $answer_b;
                    break;
                case "C":
                    $answer = $answer_c;
                    break;
                default:
                    $answer = $answer_d;
            }

            try {
                DB::beginTransaction();

                $question = questions::create([
                    'subject_id' => $subject_id,
                    'question_content' => $question_content,
                    'level_id' => $level_id,
                    'grade_id' => $grade_id,
                    'unit' => $unit,
                    'answer_a' => $answer_a,
                    'answer_b' => $answer_b,
                    'answer_c' => $answer_c,
                    'answer_d' => $answer_d,
                    'correct_answer' => $answer,
                    'suggest' => $suggest,
                    'teacher_id' => $teacher_id,
                ]);
                DB::commit();

                $count++;
            } catch (\Exception $e) {
                DB::rollBack();
                $err_list .= $stt . ', ';
            }
        }
        if ($err_list == '') {
            $result['status_value'] = "Thêm thành công " . $count . ' câu hỏi!';
            $result['status'] = 1;
        } else {
            $result['status_value'] = "Lỗi! Không thể thêm câu hỏi có STT: " . $err_list . ', vui lòng xem lại.';
            $result['status'] = 0;
        }
        return response()->json($result);
    }

    public function destroyQuestion(Request $request, $question_id)
    {
        $validator = Validator::make($request->all(), [
            'question_id' => 'required|exists:questions,question_id'
        ], [
            'question_id.exists' => 'Câu hỏi có vẻ không tồn tại!',
            'question_id.required' => 'ID câu hỏi là bắt buộc!',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        $desTroy = questions::where('question_id', $question_id)->delete();
        if ($desTroy) {
            return response()->json([
                'status' => true,
                'message' => "Xóa câu hỏi thành công!",
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => "Câu hỏi không tồn tại!",
        ], 404);
    }

    public function updateQuestion(Request $request, $question_id)
    {
        $validator = Validator::make($request->all(), [
            'question_id'       => 'required|integer|exists:questions,question_id',
            'question_content'  => 'nullable|string',
            'level_id'          => 'nullable|integer|exists:levels,level_id',
            'answer_a'          => 'nullable|string',
            'answer_b'          => 'nullable|string',
            'answer_c'          => 'nullable|string',
            'answer_d'          => 'nullable|string',
            'correct_answer'    => 'nullable|in:A,B,C,D',
            'grade_id'          => 'nullable|integer|exists:grades,grade_id',
            'unit'              => 'nullable|string',
            'suggest'           => 'nullable|string',
            'status_id'         => 'nullable|integer|in:1,2,3',
            'teacher_id'        => 'nullable|integer|exists:teachers,teacher_id',
        ], [
            'question_id.required'      => 'ID câu hỏi là bắt buộc!',
            'question_id.exists'        => 'Không tìm thấy câu hỏi với ID đã chọn!',
            'level_id.exists'           => 'Không tìm thấy level với ID đã chọn!',
            'grade_id.exists'           => 'Không tìm thấy grade với ID đã chọn!',
            'status_id.in'              => 'Cấp độ không được để trống!',
            'teacher_id.integer'        => 'Teacher ID phải là số nguyên!',
            'teacher_id.exists'         => 'Không tìm thấy giáo viên với ID đã chọn!',
            'correct_answer.in'         => 'Đáp án đúng phải là A, B, C hoặc D!',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        $question = questions::find($question_id);

        if (!$question) {
            return response()->json([
                'status' => false,
                'message' => 'Câu hỏi không tồn tại!',
            ], 404);
        }

        $question->update($request->all());

        return response()->json([
            'status' => true,
            'message' => 'Cập nhật câu hỏi thành công!',
        ], 200);
    }

    public function multiDeleteQuestion(Request $request, $question_ids)
    {
        try {
            DB::beginTransaction();

            questions::whereIn('question_id', $question_ids)->delete();

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }

    public function listClass(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'class_id' => 'integer|unique:classes,class_id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'class_id không hợp lệ',
            ], 400);
        }

        $class_id = $request->input('class_id', '2');

        $classDetail = student::select('students.student_id', 'students.avatar', 'students.username', 'students.name', 'students.birthday', 'genders.gender_detail', 'students.last_login', 'classes.class_name')
            ->join('genders', 'genders.gender_id', '=', 'students.gender_id')
            ->join('classes', 'students.class_id', '=', 'classes.class_id')
            ->where('students.class_id', $class_id)
            ->get();

    if ($classDetail->isNotEmpty()) {
        return response()->json([
            'status' => true,
            'message' => 'Lấy dữ liệu Lớp thành công!',
            'data' => $classDetail
        ], 200);
    }
    else
        return response()->json(['error' => 'Giáo viên không có lớp'], 404);
    }

    public function listClassByTeacher(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'teacher_id' => 'integer|exists:teachers,teacher_id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Giáo viên không hợp lệ',
            ], 400);
        }

        $teacher_id = $request->teacher_id;
        $data = classes::select('classes.class_id', 'classes.class_name', 'grades.detail as grade')
            ->join('grades', 'grades.grade_id', '=', 'classes.grade_id')
            ->where('teacher_id', $teacher_id)
            ->get();

        if ($data->isNotEmpty()) {
                return response()->json([
                    'status' => true,
                    'message' => 'Lấy dữ liệu Lớp này thành công!',
                    'data' => $data
                ]);
            }
            return response()->json([
                'status'    => false,
                'message'   => 'Giáo viên không có lớp',
            ]);
    }
}
