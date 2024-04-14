<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\tests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class TBMDuyetDeThiController extends Controller
{
    public function duyetDT(Request $request){
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
    public function khongDuyetDT(Request $request){
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
