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
            $result['status_value'] = "Đề thi đã được duyệt thành công!";
            $result['status_id'] = 4;
        } else {
            $result['status_value'] = "Đề thi không được duyệt!";
            $result['status_id'] = 5;
        }
        echo json_encode($result);
    }
}
