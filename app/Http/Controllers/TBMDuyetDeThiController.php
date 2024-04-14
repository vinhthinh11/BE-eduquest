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
            $new_status = $test->status_id == 5 ? 4 : 5;
            $test->status_id = $new_status;
            $test->save();
            return response()->json([
                'status_value' => $new_status == 4 ? "Đề thi đã được duyệt thành công!" : "Đề thi đã được chuyển sang trạng thái chờ duyệt!",
                'status_id' => $new_status
            ]);
        } else {
            return response()->json([
                'status_value' => "Không tìm thấy đề thi!",
                'status_id' => -1 
            ], 404);
        }

    }
}
