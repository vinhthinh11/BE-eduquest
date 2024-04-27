<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminClassController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SubjectHeadController;
use App\Http\Controllers\Admincontroller;
use App\Http\Controllers\AdminTBMonController;
use App\Http\Controllers\AdminMonHocController;
use App\Http\Controllers\AdminTeacherController;
use App\Http\Controllers\StatistController;
use App\Http\Controllers\TeacherConTroller;
use App\Http\Controllers\AdminHSController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\HSLuyenDeController;
use App\Http\Controllers\TBMDuyetDeThiController;
use App\Http\Controllers\AdminNotificationController;


Route::post('login', [AuthController::class, 'login']);
Route::get('me', [AuthController::class, 'me']);

// ----- Route for Admin -----
Route::group(['prefix' => '/admin', 'middleware' => 'admin'], function () {
    //Profile
    Route::get('/info/{username}',    [Admincontroller::class, 'getInfo'])->name('getInfo');
    Route::post('/update-profile',     [Admincontroller::class, 'updateProfile'])->name('updateProfile');

    //ql Admin
    Route::group(['prefix' => 'admin'], function () {
    Route::get('/get', [Admincontroller::class, 'getAdmin'])->name('getAdmin');
    Route::post('/create', [AdminController::class, 'createAdmin'])->name('createAdmin');
    Route::delete('/delete', [AdminController::class, 'deleteAdmin'])->name('deleteAdmin');
    Route::put('/update', [AdminController::class, 'updateAdmin'])->name('updateAdmin');
    Route::post('/file', [AdminController::class, 'check_add_admin_via_file'])->name('admin.check_add_admin_via_file');
    });

    //ql Question
    Route::group(['prefix' => 'question'], function () {
        Route::post('/create', [Admincontroller::class, 'checkAddQuestions'])->name('checkAddQuestion');
        Route::get('/get', [Admincontroller::class, 'getQuestion'])->name('getQuestion');
        Route::put('/update', [Admincontroller::class, 'updateQuestions'])->name(('updateQuestions'));
        Route::delete('/delete', [Admincontroller::class, 'deleteQuestion'])->name(('deleteQuestion'));
        Route::get('/get-grade', [Admincontroller::class, 'getGrades'])->name('getGrade');
        Route::get('/get-status', [Admincontroller::class, 'getStatus'])->name('getStatus');
        Route::get('/get-subjects', [Admincontroller::class, 'getSubjects'])->name('getSubjects');
        Route::get('/get-level', [Admincontroller::class, 'getLevels'])->name('getLevel');
        Route::post('/search', [Admincontroller::class, 'search'])->name('search');
        Route::post('/check-add-question-via-file', [AdminController::class, 'checkAddQuestionViaFile'])->name('admin.check_add_question_via_file');
    });
    //  ql test
    Route::group(['prefix' => 'test'], function () {
        Route::post('/create', [Admincontroller::class, 'checkAddTest'])->name(('checkAddTest'));
        Route::get('/get', [Admincontroller::class, 'getTest'])->name(('getTest'));
        Route::get('/detail/{test_code}', [Admincontroller::class, 'getTestDetail'])->name(('getTestDetail'));
    });

    //Statist
    Route::group(['prefix' => 'statist'], function () {
        Route::post('/list',         [StatistController::class, 'statist'])->name('statist');
        Route::post('/list-scores', [StatistController::class, 'statistScores'])->name('statistScores');
        Route::post('/list-test', [StatistController::class, 'listStatistTest'])->name('listStatistTest');
    });
    //ql Teacher
    Route::group(['prefix' => 'teacher'], function () {
        Route::get('/get',     [AdminTeacherController::class, 'getTeacher'])->name('getTeacher');
        Route::delete('/delete', [AdminTeacherController::class, 'destroy'])->name('destroyTeacher');
        Route::put('/update',   [AdminTeacherController::class, 'edit'])->name('editTeacher');
        Route::post('/create', [AdminTeacherController::class, 'create'])->name('createTeacher');
        Route::post('/search', [AdminTeacherController::class, 'search'])->name('searchTeacher');
        Route::post('/delete-check-box', [AdminTeacherController::class, 'deleteCheckbox'])->name('deleteCheckbox');
        Route::post('/file', [AdminTeacherController::class, 'createFileTeacher'])->name('createFileTeacher');
    });

    //ql Class
    Route::group(['prefix' => 'class'], function () {
        Route::get('/get',     [AdminClassController::class, 'getClasses'])->name('getClasses');
        Route::delete('/delete', [AdminClassController::class, 'destroy'])->name('destroyClass');
        Route::put('/update',   [AdminClassController::class, 'edit'])->name('editClass');
        Route::post('/create', [AdminClassController::class, 'create'])->name('createClass');
        Route::post('/search', [AdminClassController::class, 'search'])->name('searchClass');
        Route::delete('/delete-check-box', [AdminClassController::class, 'deleteCheckbox'])->name('deleteCheckbox');
    });

    //ql TBM
    Route::group(['prefix' => '/subject-head'], function () {
        Route::get('/get', [AdminTBMonController::class, 'index'])->name('index');
        Route::put('/update', [AdminTBMonController::class, 'updateTBM'])->name('updateTBM');
        Route::post('/create', [AdminTBMonController::class, 'createTBM'])->name('createTBM');
        Route::delete('/delete', [AdminTBMonController::class, 'deleteTBM'])->name('deleteTBM');
        Route::post('/file', [AdminTBMonController::class, 'check_add_tbm_via_file'])->name('check_add_tbm_via_file');
    });

    //ql Môn học
    Route::group(['prefix' => '/subject'], function () {
        Route::get('/get', [AdminMonHocController::class, 'index'])->name('index');
        Route::post('/create', [AdminMonHocController::class, 'createMon'])->name('createMon');
        Route::delete('/delete', [AdminMonHocController::class, 'deleteMon'])->name('deleteMon');
        Route::put('/update', [AdminMonHocController::class, 'updateMon'])->name('updateMon');
        Route::post('/search', [AdminMonHocController::class, 'search'])->name('search');
    });

    //ql học sinh
    Route::group(['prefix' => '/student'], function () {
        Route::get('/get', [AdminHSController::class, 'index'])->name('index');
        Route::post('/create', [AdminHSController::class, 'createHS'])->name('createHS');
        Route::delete('/delete', [AdminHSController::class, 'deleteHS'])->name('deleteHS');
        Route::put('/update', [AdminHSController::class, 'updateHS'])->name('updateHS');
        Route::post('/file', [AdminHSController::class, 'check_add_hs_via_file'])->name('check_add_hs_via_file');
    });

    //notification
    Route::get('/list-notification-teacher', [AdminNotificationController::class, 'listNotificationGV'])->name('listNotificationGV');
    Route::get('/list-notification-student', [AdminNotificationController::class, 'listNotificationHS'])->name('listNotificationHS');
    Route::post('/send-notification', [AdminNotificationController::class, 'sendNotification'])->name('sendNotification');
});

// ----- Route for Student -----
Route::group(['prefix' => '/student', 'middleware' => 'student'], function () {
    //Profile
    Route::get('/info/{username}', [StudentController::class, 'getInfo'])->name('getInfo');
    Route::post('/update-profile',      [StudentController::class, 'updateProfile'])->name('updateProfile');
    Route::get('/score/get', [StatistController::class, 'statistScoreStudent'])->name('statistScoreStudent');

    //Statist
    Route::group(['prefix' => 'statist'], function () {
        Route::get('/get',         [StatistController::class, 'statistStudent'])->name('statistStudent');
    });
    Route::group(['prefix' => '/test'], function () {
        Route::get('/get', [StudentController::class, 'getTest'])->name('getTest');
        Route::get('/get/{test_code}', [StudentController::class, 'getTestDetail'])->name('getTestDetail');
        Route::get('/addTest', [Admincontroller::class, 'addTest'])->name('addTest');
        Route::post('/update-timing', [StudentController::class, 'updateTiming'])->name('updateTiming');
        Route::post('/update-doing-exam', [StudentController::class, 'updateDoingExam'])->name('updateDoingExam');
        Route::post('/update-answer', [StudentController::class, 'updateAnswer'])->name('updateAnswer');
        Route::post('/reset-doing-exam', [StudentController::class, 'resetDoingExam'])->name('resetDoingExam');
        Route::post('/get-practice', [StudentController::class, 'getPractice'])->name('getPractice');
        Route::post('/accpet-exam', [StudentController::class, 'acceptTest'])->name('acceptTest');
        // Route::post('/accpet-practice', [StudentController::class, 'acceptPractice'])->name('acceptPractice');
        Route::get('/show-result-test', [StudentController::class, 'showResult'])->name('acceptTest');
        Route::post('/accpet-exam', [StudentController::class, 'accpectExam'])->name('accpectExam');
        Route::post('/accpet-practice', [StudentController::class, 'acceptPractice'])->name('acceptPractice');
    });


    //học sinh luyện đề
    Route::group(['prefix' => '/practice'], function () {
        Route::get('/get', [HSLuyenDeController::class, 'list'])->name('list');
        Route::post('/check-practice', [HSLuyenDeController::class, 'checkPractice'])->name('checkPractice');
        Route::post('/add-practice', [HSLuyenDeController::class, 'addPractice'])->name('addPractice');
        Route::post('/accept-practice', [HSLuyenDeController::class, 'acceptPractice'])->name('acceptPractice');
        Route::post('/show-practice', [HSLuyenDeController::class, 'showPractice'])->name('showPractice');
    });

    //xem danh sách thông báo
    Route::get('/get-notification', [StudentController::class, 'getNotification'])->name('getNotification');
});

// ----- Route for Teacher -----
Route::group(['prefix' => '/teacher', 'middleware' => 'teacher'], function () {
    // teacher qly test
    Route::group(['prefix' => '/test'], function () {
        // teacher quan ly de thi
        Route::get('/get', [TeacherConTroller::class,'getTest'])->name('teacherGetTest');
        Route::get('/get/{test_code}', [TeacherConTroller::class,'getTestDetail'])->name('teacherGetTestDetail');
        Route::post('/create', [TeacherConTroller::class,'createTest'])->name('teacherCreateTest');
        Route::put('/update/{test_code}', [TeacherConTroller::class,'updateTest'])->name('teacherUpdateTest');
        Route::delete('/delete/{test_code}', [TeacherConTroller::class,'deleteTest'])->name('teacherDeleteTest');
        Route::post('/search', [TeacherConTroller::class,'searchOfTest'])->name('teachersearchOfTest');
        Route::post('/file', [TeacherConTroller::class,'addFileTest'])->name('teacheraddFileTest');
    });

    // qly câu hỏi
    Route::group(['prefix' => '/question'], function () {
        Route::post('/create', [TeacherConTroller::class, 'addQuestion'])->name('addQuestion');
        Route::get('/get', [TeacherConTroller::class, 'getQuestion'])->name('addQuestion');
        Route::get('/getTotal', [TeacherConTroller::class, 'getTotalQuestions'])->name('addQuestion');
        Route::delete('/delete', [TeacherConTroller::class, 'destroyQuestion'])->name('destroyQuestion');
        Route::put('/update', [TeacherConTroller::class, 'updateQuestion'])->name('updateQuestion');
        Route::post('/multi-delete-question', [TeacherConTroller::class, 'multiDeleteQuestion'])->name('multiDeleteQuestion');
        Route::post('/file', [TeacherConTroller::class, 'addFileQuestion'])->name('addFileQuestion');
        Route::post('/search', [TeacherConTroller::class, 'searchOfTeacher'])->name('searchOfTeacher');
    });

    //Profile
    Route::get('/info/{username}', [TeacherConTroller::class, 'getInfo'])->name('getInfo');
    Route::post('/update-profile',      [TeacherConTroller::class, 'updateProfile'])->name('updateProfile');


    // qly điểm
    Route::group(['prefix' => '/score'], function () {
        Route::get('/get', [TeacherConTroller::class, 'getScore'])->name('getScore');
        Route::post('/export', [TeacherConTroller::class, 'exportScore'])->name('exportScore');
    });

    // qly lớp
    Route::group(['prefix' => '/class'], function () {
        Route::get('/get', [TeacherConTroller::class, 'getClass'])->name('getClass');

    });
    Route::group(['prefix' => '/student'], function () {
        Route::get('/get', [TeacherConTroller::class, 'getStudent'])->name('getClass');

    });

    // Chat with teacher
    Route::group(['prefix' => '/notification'], function () {
        Route::get('/to-student', [TeacherConTroller::class, 'getNotificationToStudent'])->name('getNotificationToStudent');
        Route::get('/by-admin', [TeacherConTroller::class, 'getNotificationByAdmin'])->name('getNotificationByAdmin');
        Route::post('/send', [TeacherConTroller::class, 'sendNotification'])->name('sendNotification');
    });
});

// ----- Route for Subject_Head -----
Route::group(['prefix' => '/subject-head', 'middleware' => 'head_subject'], function () {
    //Profile
    Route::get('/info/{username}', [SubjectHeadController::class, 'getInfo'])->name('getInfo');
    Route::post('/update-profile',      [SubjectHeadController::class, 'updateProfile'])->name('updateProfile');

    Route::get('/', [AdminTBMonController::class, 'index'])->name('index');

    //duyệt đề thi
    Route::group(['prefix' => '/test'], function () {
        Route::get('/get', [TBMDuyetDeThiConTroller::class, 'getTests'])->name('getTest');
        Route::get('/get/{test_code}', [TBMDuyetDeThiConTroller::class, 'getTestDetail'])->name('duyetDT');
        Route::put('/update/{test_code}', [TBMDuyetDeThiConTroller::class, 'updateTest'])->name('updateTest');
    });
});
