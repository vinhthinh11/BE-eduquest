<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\subjects;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\IOFactory;

class AdminMonHocController extends Controller
{
    public $successStatus = 200;
    public function index()
    {
        $getAllMon = subjects::all();
        return response()->json([
            'response' => 'success',
            'data' => $getAllMon
        ], $this->successStatus); 
    }

    public function updateMon(Request $request){
        $mon = subjects::find($request->subject_id);
        if ($mon) {
            $data = $request->all();
            $mon->update($data);

            return response()->json([
                'status'    => true,
                'message'   => 'Cập nhật môn thành công!',
            ]);
        } else {
            return response()->json([
                'status'    => false,
                'message'   => 'Không tìm thấy môn!',
            ]);
        }
    }

    public function deleteMon(Request $request)
    {
        $mon = subjects::find($request->subject_id);
        if ($mon) {
            $mon->delete();
            return response()->json([
                'status'    => true,
                'message'   => 'Xoá môn thành công!',
            ]);
        } else {
            return response()->json([
                'status'    => false,
                'message'   => 'Không tìm môn!',
            ], 404);
        }
    }

    public function createMon(Request $request){
        $result = [];
        $name = $request->input('subject_detail');
        if ($name !== null) {
            $mon = new subjects([
                'subject_detail' => $name
            ]);
            if ($mon->save()) {
                $result = $mon->toArray();
                $result['status_value'] = "Thêm thành công!";
                $result['status'] = 1;
            } else {
                $result['status_value'] = "Lỗi! Môn học đã tồn tại!";
                $result['status'] = 0;
            }
        } else {
            $result['status_value'] = "Lỗi! Tên môn học không được trống!";
            $result['status'] = 0;
        }
        return response()->json([
            'result' => $result,
        ]);
    }
}
