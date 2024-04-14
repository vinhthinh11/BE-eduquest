<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\tests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class TBMDuyetDTController extends Controller
{
    public function duyetDeThi(Request $request){
        $result = array();
        $status_id = $request->status_id;
        $test_code = $request->test_code;
        $test = tests::where('test_code', $test_code)->where('status_id', $status_id)->first();
        if ($test) {
            return response()->json([
                'status_value' => "Đề thi đã được duyệt thành công!",
                'status_id' => 4
            ]);
            
        } else {
            return responese()->json([
                'status_value' => "Đề thi không được duyệt!",
                'status_id' => 5
            ]);
        }
    }
}
