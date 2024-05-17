<?php

namespace App\Http\Controllers;

use App\Models\classes;
use App\Models\notifications;
use App\Models\quest_of_practice;
use App\Models\quest_of_test;
use App\Models\questions;
use App\Models\scores;
use App\Models\student;
use App\Models\student_notifications;
use App\Models\students;
use App\Models\teacher;
use App\Models\tests;
use Carbon\Carbon;
use Carbon\CarbonTimeZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Illuminate\Support\Facades\Validator;

class TeacherConTroller extends Controller
{
    public function searchOfTest(Request $request)
    {
    $keySearch = $request->key_search;

    $data = tests::where('test_code', 'like', '%' . $keySearch . '%')
                ->orWhere('test_name', 'like', '%' . $keySearch . '%')
                ->orWhere('subject_id', 'like', '%' . $keySearch . '%')
                ->orWhere('grade_id', 'like', '%' . $keySearch . '%')
                ->orWhere('level_id', 'like', '%' . $keySearch . '%')
                ->orWhere('note', 'like', '%' . $keySearch . '%')
                ->get();

        return response()->json([
            'data'  => $data
        ]);
    }
    public function searchOfTeacher(Request $request)
    {
    $keySearch = $request->key_search;

    $data = questions::where('question_content', 'like', '%' . $keySearch . '%')
                    ->orWhere('answer_a', 'like', '%' . $keySearch . '%')
                    ->orWhere('answer_b', 'like', '%' . $keySearch . '%')
                    ->orWhere('answer_c', 'like', '%' . $keySearch . '%')
                    ->orWhere('answer_d', 'like', '%' . $keySearch . '%')
                    ->orWhere('suggest', 'like', '%' . $keySearch . '%')
                    ->get();

        return response()->json([
            'data'  => $data
        ]);
    }
    public function getInfo(Request $request)
    {
        $username = $request->user('teachers')->username;
        $me = teacher::select('teachers.teacher_id', 'teachers.username', 'teachers.avatar', 'teachers.email', 'teachers.name', 'teachers.last_login', 'teachers.birthday', 'permissions.permission_detail', 'genders.gender_detail', 'genders.gender_id')
            ->join('permissions', 'teachers.permission', '=', 'permissions.permission')
            ->join('genders', 'teachers.gender_id', '=', 'genders.gender_id')
            ->where('teachers.username', '=', $username)
            ->first();

        return response()->json([
            'message' => 'Lấy thông tin cá nhân thành công!',
            'data' => $me
        ], 200);
    }
    public function updateProfile(Request $request)
    {
        $me = $request->user('teachers');
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|min:3|max:255',
            'gender_id' => 'nullable|integer',
            'birthday' => 'nullable|date',
            'password' => 'nullable|min:6|max:20',
            'email' => 'nullable|email|unique:admins,email',
            'avatar' => 'nullable|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        $data = $request->only(['name', 'gender_id', 'birthday', 'email', 'permission']);

        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        }

        if ($request->hasFile('avatar')) {
            if ($me->avatar != "avatar-default.jpg") {
                Storage::delete('public/' . str_replace('/storage/', '', $me->avatar));
            }
            $image = $request->file('avatar');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('images',  $imageName, 'public');
            $data['avatar'] = '/storage/' . $imagePath;
        }
        $me->update($data);

        if ($request->filled('password')) {
            return response()->json([
                'message' => "Thay đổi mật khẩu thành công thành công!",
            ], 200);
        } else {
            return response()->json([
                'message' => "Cập nhập tài khoản cá nhân thành công!",
                'data' => $me
            ], 201);
        }
    }

    public function getStudent(Request $request)
    {
        $user = $request->user('teachers');
        //get all student that have class_id in classes that teacher_id is $user->teacher_id
        $students = students::join('classes', 'students.class_id', '=', 'classes.class_id')
            ->where('classes.teacher_id', $user->teacher_id)

            ->select("name", "username",'email', "students.student_id", "students.class_id","students.birthday","avatar")->get();
        // ở đây có trường hợp giáo viên chưa chủ nhiệm lớp nào thì số học sinh trả về sẽ là 0
        return response()->json([
            'message' => 'Lấy dữ liệu Lớp thành công!',
            'data' => $students
        ], 200);
    }
     public function getStudentOfClass(Request $request,$class_id)
    {
        $user = $request->user('teachers');
        //get all student that have class_id in classes that teacher_id is $user->teacher_id
        $students = students::join('classes', 'students.class_id', '=', 'classes.class_id')
            ->where('classes.teacher_id', $user->teacher_id)
            ->where('students.class_id', $class_id)
            ->select("name", "username",'email', "students.student_id", "students.class_id","students.birthday","avatar")->get();
        // ở đây có trường hợp giáo viên chưa chủ nhiệm lớp nào thì số học sinh trả về sẽ là 0
        return response()->json([
            'message' => 'Lấy dữ liệu Lớp thành công!',
            'data' => $students
        ], 200);
    }
    public function getClass(Request $request)
    {
        $user = $request->user('teachers');
        $classes = classes::with('teacher')->where('teacher_id', $user->teacher_id)->get();

        return response()->json([
            'message'   => 'Lấy dữ liệu lớp thành công!',
            'data'      => $classes
        ], 200);
    }
    public function getClassByTeacher(Request $request)
    {
        $user = $request->user('teachers');
        $validator = Validator::make($request->all(), [
            'teacher_id' => 'integer|exists:teachers,teacher_id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Giáo viên không hợp lệ',
            ], 422);
        }

        $classes = classes::join('grades', 'grades.grade_id', '=', 'classes.grade_id')
            ->select('classes.class_id', 'classes.class_name', 'grades.detail as grade')
            ->where('teacher_id', $user->teacher_id)
            ->get();
        return response()->json([
            'status'    => true,
            'message'   => 'Lấy dữ liệu lớp thành công!',
            'data'      => $classes
        ], 200);
    }
    //show điểm
    public function getScore(Request $request)
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
            'file' => 'required|file|mimes:xlsx',
        ], [
            'file.required' => 'Vui lòng chọn tệp để tiếp tục!',
            'file.mimes' => 'File phải là xlsx!',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $result = [];
        $subjectId = $request->subject_id;
        $inputFileType = 'Xlsx';
        $count = 0;
        $errList = [];

        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->path();

            $reader = IOFactory::createReader($inputFileType);
            $spreadsheet = $reader->load($filePath);
            $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

            foreach ($sheetData as $key => $row) {
                if ($key < 4 || empty($row['A'])) {
                    continue;
                }

                $stt = $row['A'];
                $questionContent = $row['B'];
                $levelId = $row['C'];
                $answerA = $row['D'];
                $answerB = $row['E'];
                $answerC = $row['F'];
                $answerD = $row['G'];
                $correctAnswer = $row['H'];
                $gradeId = $row['I'];
                $unit = $row['J'];
                $suggest = $row['K'];
                $teacherId = null;

                switch ($correctAnswer) {
                    case "A":
                        $answer = $answerA;
                        break;
                    case "B":
                        $answer = $answerB;
                        break;
                    case "C":
                        $answer = $answerC;
                        break;
                    default:
                        $answer = $answerD;
                }

                $dataToValidate = [
                    'subject_id' => $subjectId,
                    'question_content' => $questionContent,
                    'level_id' => $levelId,
                    'answer_a' => $answerA,
                    'answer_b' => $answerB,
                    'answer_c' => $answerC,
                    'answer_d' => $answerD,
                    'correct_answer' => $answer,
                    'grade_id' => $gradeId,
                    'unit' => $unit,
                    'suggest' => $suggest,
                    'status_id' => 3,
                    'teacher_id' => $teacherId,
                ];

                $customValidator = Validator::make($dataToValidate, [
                    'subject_id' => 'required|integer',
                    'question_content' => 'required|string',
                    'level_id' => 'required|integer|exists:levels,level_id',
                    'answer_a' => 'required|string',
                    'answer_b' => 'required|string',
                    'answer_c' => 'required|string',
                    'answer_d' => 'required|string',
                    'correct_answer' => 'required|string|in:A,B,C,D',
                    'grade_id' => 'required|integer|exists:grades,grade_id',
                    'unit' => 'required|string',
                    'suggest' => 'nullable|string',
                    'status_id' => 'required|integer',
                    'teacher_id' => 'nullable|integer',
                ]);

                if ($customValidator->fails()) {
                    $errList[] = $stt;
                    continue;
                }

                $question = new questions($dataToValidate);

                if ($question->saveQuietly()) {
                    $count++;
                } else {
                    $errList[] = $stt;
                }
            }

            unlink($filePath);

            if (empty($errList)) {
                $result['status_value'] = "Thêm thành công " . $count . " câu hỏi!";
                $result['status'] = 1;
            } else {
                $result['status_value'] = "Lỗi! Không thể thêm câu hỏi có STT: " . implode(', ', $errList) . ', vui lòng xem lại.';
                $result['status'] = 0;
            }
        } else {
            $result['status_value'] = "Không tìm thấy tệp được tải lên!";
            $result['status'] = 0;
        }

        return response()->json($result);
    }

    public function destroyQuestion(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'question_id' => 'required|integer|exists:questions,question_id',
        ], [
            'question_id.required' => 'Câu hỏi chưa đúng ID!',
            'question_id.exists' => 'Câu hỏi không tồn tại!',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors(),
            ], 422);
        }
        $question = questions::find($request->question_id);

        if (!$question) {
            return response()->json([
                'status'  => false,
                'message' => 'Câu hỏi không tồn tại!'
            ]);
        }
        try {
            $question->delete();

            return response()->json([
                'status'  => true,
                'message' => 'Xóa câu hỏi thành công!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Câu hỏi đang tồn tại ở ngân hàng câu hỏi!',
                'data'    => $e
            ]);
        }
    }

    public function updateQuestion(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'question_id'       => 'required|integer|exists:questions,question_id',
            'question_content'  => 'nullable|string',
            'level_id'          => 'required|nullable|integer|exists:levels,level_id',
            'answer_a'          => 'nullable|string',
            'answer_b'          => 'nullable|string',
            'answer_c'          => 'nullable|string',
            'answer_d'          => 'nullable|string',
            'correct_answer'    => 'nullable',
            'grade_id'          => 'nullable|integer|exists:grades,grade_id',
            'unit'              => 'nullable|string',
            'suggest'           => 'nullable|string',
            'status_id'         => 'nullable|integer|in:1,2,3',
            'teacher_id'        => 'nullable|integer|exists:teachers,teacher_id',
        ], [
            'question_id.required'      => 'ID câu hỏi là bắt buộc!',
            'question_id.exists'        => 'Không tìm thấy câu hỏi với ID đã chọn!',
            'level_id.exists'           => 'Không tìm thấy level với ID đã chọn!',
            'level_id.required'         => 'Level bắt buộc!',
            'grade_id.exists'           => 'Không tìm thấy grade với ID đã chọn!',
            'status_id.in'              => 'Cấp độ không được để trống!',
            'teacher_id.integer'        => 'Teacher ID phải là số nguyên!',
            'teacher_id.exists'         => 'Không tìm thấy giáo viên với ID đã chọn!',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        $question = questions::find($request->question_id);

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
    public function getQuestion(Request $request)
    {
        $user = $request->user('teachers');
        $questions = questions::where('subject_id', $user->subject_id)->get();
        return response()->json(["data" => $questions]);
    }
    public function getTotalQuestions(Request $request)
    {
        $user = $request->user('teachers');
        $numQuestion = DB::table('questions')
            ->select(DB::raw('count(question_id) as total_question, level_id, subject_id'))
            ->where('subject_id', $user->subject_id)
            ->groupBy('subject_id', 'level_id')
            ->get();

        return response()->json(["data" => $numQuestion]);
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
    /**
     * 1. Giáo viên môn nào thì chỉ xem được để của môn đó
     */
    public function getTest(Request $request)
    {
        // teacher môn nào chỉ có thể xem test của môn đó
        $id = $request->user('teachers')->subject_id;
        $data  = tests::with('subject')->where('subject_id', $id)->orderBy('timest', 'desc')->get();
        return response()->json(["data" => $data]);
    }
    /**
     * Xem chi tiết đề thi
     */
    public function getTestDetail(Request $request, $test_code)
    {
        // teacher môn nào chỉ có thể xem test của môn đó
        $questions = [];
        $data  = tests::find($test_code);
        if (!$data) return response()->json(["message" => "Không tìm thấy đề thi!"], 400);
        foreach ($data->questions as $question) {
            $questions[] = $question;
        }
        $data['questions'] = $questions;

        return response()->json(["data" => $data]);
    }
    /**
     * 1. Chỉ delete những đề chưa duyệt, và đề nào đã duyệt rồi thi không xóa được
     * 2. Chỉ delete những đề của môn học mà giáo viên đó dạy
     */
    public function deleteTest(Request $request, $test_code)
    {
        $id = $request->user('teachers');
        $test = tests::find($test_code);

        // kiểm tra xem đề thi có tồn tại không
        if (!$test) return response()->json(["message" => "Không tìm thấy đề thi!"], 400);

        // kiểm tra xem đề thi có phải của môn học mà giáo viên dạy không
        if ($test->subject_id != $id->subject_id) return response()->json(["message" => "Đề thi không phải của môn học mà giáo viên dạy!"], 400);

        // kiểm tra xem đề thi đã duyệt chưa
        if ($test->status_id != 3) return response()->json(["message" => "Đề thi đã duyệt không thể xóa!"], 400);
        $test->delete();
        return response()->json(["message" => "Xóa thành công đề thi", "data" => $test]);
    }
    /**
     * 1. Chỉ update những đề chưa duyệt, và đề nào đã duyệt rồi thi không update được
     * 2. Chỉ update những đề của môn học mà giáo viên đó dạy
     * 3. Số lượng câu hỏi sẽ không thay đổi được tại vì khi tạo đề từ số lượng câu hỏi sẽ sinh ra chi tiết đề
     * 4. Khi update đề thì chỉ update được password của đề thi, thời gian làm bài, ghi chú, tên đề thi
     */
    public function updateTest(Request $request, $test_code)
    {
        $validator = Validator::make($request->all(), [
            'time_to_do'   => 'sometimes|numeric|min:15|max:120',
            'password'      => 'sometimes|string|min:6|max:20',
            'note'          => 'sometimes|string',
        ], [
            'teacher_id.exists' => 'Không tìm thấy Giáo viên!',
            'subject_id.exists' => 'Không tìm thấy Môn học!',
            'time_to_do.min' => 'Thời gian tối thiểu 15 phút!',
            'password.min' => 'Password tối thiểu 6 kí tự!',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }
        $data = $request->all();
        if ($request->password)
            $data['password'] = bcrypt($request->password);
        $test  = tests::find($test_code)
            ->update($data);

        return response()->json([
            'message' => 'Cập nhật đề thi thành công!',
            "data" => $test
        ]);
    }

    /**
     * Tạo đề tự động số câu hỏi sẽ được lấy ngẫu nhiên từ ngân hàng câu hỏi, dựa theo môn học, khối học, cấp độ
     */
    public function createTest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'test_name'  => 'string|unique:tests,test_name',
            'total_questions' => 'integer|min:10|max:100',
            'password'      => 'required|string|min:6|max:10',
            'grade_id' => 'integer|exists:grades,grade_id',
            'level_id' => 'required|integer|exists:levels,level_id',
            'time_to_do'   => 'required|numeric|min:10|max:120',
        ], [
            'test_name.unique'  => 'Tên đề không nên trùng nhau!',
            'password.min'      => 'Password tối thiểu 6 kí tự!',
            'grade_id.exists'   => 'Không tìm thấy Lớp!',
            'total_questions.min' => 'Tối thiểu 10 câu hỏi trong đề!',
            'level_id.required' => 'Level_id là bắt buộc!',
            'time_to_do.min' => 'Thời gian làm bài tối thiếu là 10 phút!',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }
        $user = $request->user('teachers');
        // lấy số lượng câu hỏi của giáo viên dạy môn đó trong ngân hang câu hỏi
        $numQuestion = questions::where('subject_id', $user->subject_id)->where('grade_id', $request->grade_id)->where('level_id', $request->level_id)->count();
        // kiểm tra số lượng câu hỏi trong ngân hàng đề thi có đủ hay không
        if ($numQuestion < $request->total_questions) return response()->json(["message" => "Số lượng câu hỏi trong ngân hàng câu hỏi là".$numQuestion."không đủ!"], 400);
        if ($numQuestion < $request->total_questions) return response()->json(["message" => "Số lượng câu hỏi trong ngân hàng câu hỏi là".$numQuestion."không đủ!"], 400);
        $user = $request->user('teachers');
        DB::beginTransaction();
        try {
            $test_code = time();
            $data = $request->all();
            $test = (array_merge($data, ['test_code' => $test_code, 'subject_id' => $user->subject_id, 'status_id' => 3, 'password' => bcrypt($request->password)]));
            // tạo chi tiết đề thi
            $testCreate = tests::create($test);
            $questions = questions::where('subject_id', $user->subject_id)->where('grade_id', $request->grade_id)->where('level_id', $request->level_id)->inRandomOrder()->limit($request->total_questions)->get('question_id');
            foreach ($questions as $question) {
                quest_of_test::create(['test_code' => $test_code, 'question_id' => $question->question_id]);
            }
            DB::commit();
            return response()->json([
                'message' => "Tạo đề thi thành công!",
                "test" => $testCreate
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(["message" => "Tạo đề thi thất bại!", "error" => $e->getMessage()], 400);
        }
    }

    public function addFileTest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'test_code' => 'required|integer|exists:tests,test_code',
            'file'      => 'required|file|mimes:xlsx,xls',
        ], [
            'test_code.exists' => 'Không tìm thấy đề!',
            'file.mimes' => 'File phải là Excel!',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $inputFileType = 'Xlsx';

        try {
            $reader = IOFactory::createReader($inputFileType);
            $spreadsheet = $reader->load($request->file('file')->getRealPath());
            $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

            $count = 0;
            $err_list = [];

            foreach ($sheetData as $index => $row) {
                if ($index < 4 || empty($row['A'])) {
                    continue;
                }

                $stt = $row['A'];
                $test_name = $row['B'];
                $level_id = $row['C'];
                $grade_id = $row['D'];
                $total_questions = $row['E'];
                $time_to_do = $row['F'];
                $note = $row['G'];
                $status_id = $row['H'];
                $timest = $row['J'];

                if (empty($test_name) || empty($grade_id) || empty($level_id) || empty($timest) || empty($total_questions) || empty($time_to_do) || empty($note) || empty($status_id)) {
                    $err_list[] = $stt;
                    continue;
                }

                DB::beginTransaction();

                $subject_id = $request->test_code;
                $test = tests::create([
                    'subject_id' => $subject_id,
                    'test_name' => $test_name,
                    'level_id' => $level_id,
                    'grade_id' => $grade_id,
                    'timest' => $timest,
                    'total_questions' => $total_questions,
                    'time_to_do' => $time_to_do,
                    'note' => $note,
                    'status_id' => $status_id,
                    'teacher_id' => null,
                ]);

                DB::commit();
                $count++;
            }

            if (empty($err_list)) {
                $result['status_value'] = "Thêm thành công " . $count . ' bài thi!';
                $result['status'] = 1;
            } else {
                $result['status_value'] = "Lỗi! Không thể thêm bài thi có STT: " . implode(', ', $err_list) . ', vui lòng xem lại.';
                $result['status'] = 0;
            }

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }

        return response()->json($result);
    }


    public function notificationsToStudent(Request $request)
    {
        $teacherId = $request->user('teachers')->teacher_id;
        $classId = classes::where("teacher_id", $teacherId)->get();

        $notifications = Notifications::whereIn('notification_id', function ($query) use ($classId) {
            $query->select('notification_id')
                ->from('student_notifications')
                ->where('class_id', $classId);
        })->whereIn('notification_id', function ($query) use ($teacherId) {
            $query->select('notification_id')
                ->from('classes')
                ->where('teacher_id', $teacherId);
        })->orderBy('time_sent', 'desc')->get();

        return response()->json([
            'message' => 'Thông báo được truy xuất thành công!',
            'notifications' => $notifications
        ], 200);
    }
    public function notificationsByAdmin(Request $request)
    {
        $teacherId = $request->user("teachers")->teacher_id;
        $notifications = Notifications::whereIn('notification_id', function ($query) use ($teacherId) {
            $query->select('notification_id')
                ->from('teacher_notifications')
                ->where('teacher_id', $teacherId);
        })->get();

        return response()->json([
            'message' => 'Thông báo được truy xuất thành công!',
            'data' => $notifications,
        ], 200);
    }

    public function sendNotification(Request $request)
    {
        $user = $request->user('teachers');
        $validator = Validator::make($request->all(), [
            'notification_title' => 'required|string|max:255',
            'notification_content' => 'required|string',
            'class_id' => 'required|array',
            'class_id.*' => 'required|integer',
        ], [
            'notification_title.string' => 'Notification_title phải là chuỗi!',
            'notification_content.string' => 'Notification_content phải là chuỗi!',
            'notification_title.max' => 'Notification_title phải là 255 kí tự!',
            'class_id.required' => 'Chưa lớp người nhận!',
            'class_id.array' => 'Class_id phải là mảng!',
            'class_id.*.required' => 'Mỗi class_id trong mảng là bắt buộc!',
            'class_id.*.integer' => 'Mỗi class_id trong mảng phải là số nguyên!',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $notification = Notifications::create([
            'name' => $user->name,
            'username' => $user->username,
            'notification_title' => $request->notification_title,
            'notification_content' => $request->notification_content,
            'time_sent' => Carbon::now('Asia/Ho_Chi_Minh'),
        ]);
        $classNames = [];
        foreach ($request->class_id as $class_id) {
            Student_Notifications::create([
                'notification_id' => $notification->notification_id,
                'class_id' => $class_id,
            ]);
            $class = Classes::find($class_id);
            if ($class) {
                $classNames[] = $class->class_name;
            }
        }

        Log::info('Notification sent', ['notification_id' => $notification->id]);
        $id = $user->teacher_id;
        return response()->json([
            'message' => 'Gửi thông báo thành công cho các lớp: ' . implode(', ', $classNames),
            'data' => [
                'id' => $id,
                'chat' => $notification],
        ], 200);
    }
}
