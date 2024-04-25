<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\tests;
use Illuminate\Support\Facades\Validator;

class TBMDuyetDeThiController extends Controller
{
    public function getTests(Request $request)
    {
        $user = $request->user('subject_heads');
        $tests = tests::where('subject_id', $user->subject_id)->orderByDesc('timest')->get();
        return response()->json(["data"=>$tests]);
    }
    public function getTestDetail(Request $request, $test_code)
    {
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
     * Duyệt đề, hay là thay đổi trạng thái của đề
     * @param Request $request phải có status_id = 4 hoặc 5
     */
    public function updateTest(Request $request, $test_code)
    {
        $validator = Validator::make($request->all(), [
            'status_id' => 'required|integer|in:1,2,3,4,5',
        ], [
            'status_id.required' => 'Trường trạng thái là bắt buộc.',
            'status_id.integer' => 'Trường trạng thái phải là một số nguyên.',
            "status_id.in" => "Trạng thái phải thuộc các giá trị: 1, 2, 3, 4, 5.",
        ]);
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }
        $test = tests::find($test_code);
        if(!$test) return response()->json(['message' => 'Không tìm thấy đề thi'], 404);
        $test->status_id = $request->status_id;
        $test->save();
        return response()->json($test);
    }
    public function duyetDT(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'test_code' => 'required|string|unique:tests,test_code',
        ], [
            'test_code.required' => 'Trường mã đề thi là bắt buộc.',
            'test_code.string' => 'Mã đề thi phải là một chuỗi.',
            'test_code.unique' => 'Mã đề thi đã tồn tại trong hệ thống.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

       $test_code = $request->test_code;
       $test = tests::where('test_code', $test_code)->first();
       if ($test) {
           if ($test->status_id == 3 || $test->status_id == 5) {
               $test->status_id = 4;
               $test->save();
               return response()->json([
                   'status_value' => "Đề thi đã được duyệt thành công!",
                   'status_id' => 4
               ]);
           } else {
               return response()->json([
                   'status_value' => "Đề thi đang trong trạng thái duyệt!",
                   'status_id' => $test->status_id
               ], 400);
           }
       } else {
           return response()->json([
               'status_value' => "Không tìm thấy đề thi!",
               'status_id' => -1
           ], 404);
       }

    }
    public function khongDuyetDT(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'test_code' => 'required|string|unique:tests,test_code',
        ], [
            'test_code.required' => 'Trường mã đề thi là bắt buộc.',
            'test_code.string' => 'Mã đề thi phải là một chuỗi.',
            'test_code.unique' => 'Mã đề thi đã tồn tại trong hệ thống.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $test_code = $request->test_code;
        $test = tests::where('test_code', $test_code)->first();
        if ($test) {
            if ($test->status_id == 3 || $test->status_id == 4) {
                $test->status_id = 5;
                $test->save();
                return response()->json([
                    'status_value' => "Đề thi đã được được chuyển sang trạng thái không duyệt!",
                    'status_id' => 5
                ]);
            } else {
                return response()->json([
                    'status_value' => "Đề thi đang trong trạng thái không duyệt!",
                    'status_id' => $test->status_id
                ], 400);
            }
        } else {
            return response()->json([
                'status_value' => "Không tìm thấy đề thi!",
                'status_id' => -1
            ], 404);
        }

     }

}
