<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\Question\DeleteQuestionRequest;
use App\Http\Requests\Admin\Question\UpdateQuestionRequest;
use App\Http\Requests\Admin\Teacher\DeleteTeacherRequest;
use App\Http\Requests\Teacher\ExportScoreRequest;
use App\Http\Requests\Teacher\FileQuestionRequest;
use App\Http\Requests\Teacher\StoreQuestionRequest;
use App\Models\questions;
use App\Models\scores;
use App\Models\teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class TeacherConTroller extends Controller
{
    //show điểm
    public function listScore(DeleteTeacherRequest $request)
    {
    $student_id = $request->input('student_id', '1');
    $scoreData  = scores::where('student_id', $student_id)
                        ->get();

    return response()->json([
        'scoreData' => $scoreData
        ]);
    }

    //xuất file điểm
    public function exportScore(ExportScoreRequest $request)
    {
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

    public function addQuestion(StoreQuestionRequest $request)
    {
        $data = $request->validated();

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

    public function addFileQuestion(FileQuestionRequest $request)
    {
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

    public function destroyQuestion(DeleteQuestionRequest $request, $question_id)
    {
        $desTroy = questions::where('question_id', $question_id)->delete();
        if($desTroy) {
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

    public function updateQuestion(UpdateQuestionRequest $request, $question_id)
    {
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

    public function multiDeleteQuestion(Request $request ,$question_ids)
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
}
