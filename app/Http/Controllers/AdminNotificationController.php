<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\notifications;
use App\Models\admin;
use App\Models\student_notifications;
use App\Models\teacher_notifications;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class AdminNotificationController extends Controller
{
    // danh sách thông báo cho gv
    public function listNotificationGV(Request $request){
        $getListGV = notifications::whereExists(function ($query) {
            $query->select(DB::raw(1))
                ->from('teacher_notifications')
                ->whereColumn('teacher_notifications.question_id', 'notifications.question_id');
        })->get();
        if ($getListGV->isEmpty()) {
            return response()->json([
                'message' => 'Không tìm thấy dữ liệu',
            ], 400);
        }
        return response()->json([
            'message' => 'Thành công',
            'data' => $getListGV
        ]);
    }

    // danh sách thông báo cho học sinh
    public function listNotificationHS(Request $request){
        $getListHS = notifications::whereExists(function ($query) {
            $query->select(DB::raw(1))
                ->from('student_notifications')
                ->whereColumn('student_notifications.question_id', 'notifications.question_id');
        })->get();
        if ($getListHS->isEmpty()) {
            return response()->json([
                'message' => 'Không tìm thấy dữ liệu',
            ], 400);
        }
        return response()->json([
            'message' => 'Thành công',
            'data' => $getListHS
        ]);
    }

    // gửi thông báo
    public function sendNotification(Request $request){
        $result = [];
        $username = $request->username;
        $name = $request->name;
        $notification_title = $request->notification_title;
        $notification_content = $request->notification_content;
        $teacher_id = $request->teacher_id->array();
        $class_id = $request->class_id->array();
        if (empty($notification_title)||empty($notification_content)) {
            $result['status_value'] = "Nội dung hoặc tiêu đề không được trống!";
            $result['status'] = 0;
        } else {
            if (empty($teacher_id)&&empty($class_id)) {
                $result['status_value'] = "Chưa chọn người nhận!";
                $result['status'] = 0;
            } else {
                $notification = new notifications([
                    'username' => $username,
                    'name' => $name,
                    'notification_title' => $notification_title,
                    'notification_content' => $notification_content,
                    'time_sent' => now()
                ]);
                $notification->saveQuietly();
                $notificationId = $notification->notification_id;

                // Gửi thông báo cho giáo viên
                if (!empty($teacher_id)) {
                    foreach ($teacher_id as $teacherId) {
                        $sendTeacherNotification = new teacher_notifications([
                            'notification_id' => $notificationId,
                            'teacher_id' => $teacherId
                        ]);
                        $sendTeacherNotification->saveQuietly();
                    }
                }

                // Gửi thông báo cho lớp học
                if (!empty($class_id)) {
                    foreach ($class_id as $classId) {
                        $sendClassNotification = new student_notifications([
                            'notification_id' => $notificationId,
                            'class_id' => $classId
                        ]);
                        $sendClassNotification->saveQuietly();
                    }
                }

                $result['status_value'] = "Gửi thành công!";
                $result['status'] = 1;
            }
        }
        return response()->json([
            'result' => $result,
        ]);
    }
}
