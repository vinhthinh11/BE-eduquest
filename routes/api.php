<?php

use App\Http\Controllers\AdminClassController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admincontroller;
use App\Http\Controllers\AdminTBMonController;
use App\Http\Controllers\AdminMonHocController;
use App\Http\Controllers\AdminTeacherController;
use App\Http\Controllers\StatistController;
use App\Http\Controllers\TeacherConTroller;
use App\Http\Controllers\AdminHSController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\HSLuyenDeController;
use App\Http\Controllers\TBMDuyetDeThiController;


    Route::post('login', [AuthController::class, 'login']);
    Route::get('me', [AuthController::class, 'me']);

    Route::group(['prefix' => '/admin','middleware' => 'admin'], function () {
    //Profile
    Route::put('/update-profile',      [ProfileController::class, 'updateProfileAdmin'])->name('updateProfileAdmin');
    // Route::put('/update-profile',      [ProfileController::class, 'updateProfile'])->name('updateProfile');

    //ql Admin
    Route::get('/get', [Admincontroller::class, 'getAdmin'])->name('getAdmin');
    // Route::get('/index', [AdminController::class, 'indexAdmin']);
    Route::post('/create', [AdminController::class, 'createAdmin'])->name('createAdmin');
    Route::delete('/delete', [AdminController::class, 'deleteAdmin'])->name('deleteAdmin');
    Route::put('/update', [AdminController::class, 'updateAdmin'])->name('updateAdmin');
    Route::post('/check-add-admin-via-file', [AdminController::class, 'check_add_admin_via_file'])->name('admin.check_add_admin_via_file');


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
        Route::post('/check-add-question-via-file', [AdminController::class, 'checkAddQuestionViaFile'])->name('admin.check_add_question_via_file');
        Route::post('check-add-test', [Admincontroller::class, 'checkAddTest'])->name(('checkAddTest'));
 });

    //Profile
    Route::group(['prefix' => 'profiles'], function () {

        Route::post('/update-last-login',  [ProfileController::class, 'updateLastLogin'])->name('updateLastLogin');
        Route::post('/update-avatar',      [ProfileController::class, 'updateAvatar'])->name('updateAvatarProfile');
        Route::get('get-profile',         [Admincontroller::class, 'getProfiles'])->name('getProfiles');
        Route::get('admin-info{username}', [Admincontroller::class, 'getAdminInfo'])->name('getAdminInfo');
        Route::post('/teacher-info',       [ProfileController::class, 'teacherInfo'])->name('teacherInfo');
        Route::post('/student-info',       [ProfileController::class, 'studentInfo'])->name('studentInfo');
        Route::post('/subject-head-info',  [ProfileController::class, 'subjectheadInfo'])->name('subjectheadInfo');
    });

    //Thong Ke
    Route::post('/list-statist', [StatistController::class, 'listStatist'])->name('listStatist');
    Route::post('/list-statis2t-scores', [StatistController::class, 'listStatistScores'])->name('listStatistScores');

    //ql Teacher
    Route::group(['prefix' => 'teacher'], function () {
        Route::get('/get',     [AdminTeacherController::class, 'getTeacher'])->name('getTeacher');
        Route::delete('/delete', [AdminTeacherController::class, 'destroy'])->name('destroyTeacher');
        // Route::put('/update',  [AdminTeacherController::class, 'update'])->name('updateTeacher');
        Route::put('/update',   [AdminTeacherController::class, 'edit'])->name('editTeacher');
        Route::post('/create', [AdminTeacherController::class, 'create'])->name('createTeacher');
        Route::post('/search', [AdminTeacherController::class, 'search'])->name('searchTeacher');
        Route::post('/delete-check-box', [AdminTeacherController::class, 'deleteCh2eckbox'])->name('deleteCheckbox');
        Route::post('/file', [AdminTeacherController::class, 'createFileTeacher'])->name('check_add_teacher_via_file');
    });

    //ql Class
    Route::group(['prefix' => 'class'], function () {
        Route::get('/get',     [AdminClassController::class, 'getClasses'])->name('getClasses');
        Route::delete('/delete', [AdminClassController::class, 'destroy'])->name('destroyClass');
        // Route::put('/update', [AdminClassController::class, 'update'])->name('updateClass');
        Route::put('/update',   [AdminClassController::class, 'edit'])->name('editClass');
        Route::post('/create', [AdminClassController::class, 'create'])->name('createClass');
        Route::post('/search', [AdminClassController::class, 'search'])->name('searchClass');
        Route::delete('/delete-check-box', [AdminClassController::class, 'deleteCheckbox'])->name('deleteCheckbox');
    });

    //ql TBM
    Route::group(['prefix' => '/truongbomon'], function () {
        Route::get('/get', [AdminTBMonController::class, 'index'])->name('index');
        Route::put('/update', [AdminTBMonController::class, 'updateTBM'])->name('updateTBM');
        Route::post('/create', [AdminTBMonController::class, 'createTBM'])->name('createTBM');
        Route::delete('/delete', [AdminTBMonController::class, 'deleteTBM'])->name('deleteTBM');
        Route::post('/file', [AdminTBMonController::class, 'check_add_tbm_via_file'])->name('check_add_tbm_via_file');
    });

    //ql Môn học
    Route::group(['prefix' => '/mon'], function () {
        Route::get('/get', [AdminMonHocController::class, 'index'])->name('index');
        Route::post('/create', [AdminMonHocController::class, 'createMon'])->name('createMon');
        Route::delete('/delete', [AdminMonHocController::class, 'deleteMon'])->name('deleteMon');
        Route::put('/update', [AdminMonHocController::class, 'updateMon'])->name('updateMon');
    });

    //ql học sinh
    Route::group(['prefix' => '/student'], function () {
        Route::get('/get', [AdminHSController::class, 'index'])->name('index');
        Route::post('/create', [AdminHSController::class, 'createHS'])->name('createHS');
        Route::delete('/delete', [AdminHSController::class, 'deleteHS'])->name('deleteHS');
        Route::put('/update', [AdminHSController::class, 'updateHS'])->name('updateHS');
        Route::post('/file', [AdminHSController::class, 'check_add_hs_via_file'])->name('check_add_hs_via_file');
    });

});


Route::group(['prefix' => '/student', 'middleware' => 'student'], function () {
    //Profile
    Route::put('/update-profile',      [ProfileController::class, 'updateProfileStudent'])->name('updateProfileStudent');
    // Route::put('/update-profile',      [ProfileController::class, 'updateProfile'])->name('updateProfile');

    Route::get('/get', [AdminHSController::class, 'index'])->name('index');
    Route::get('/addTest', [Admincontroller::class, 'addTest'])->name('addTest');

    Route::post('/update-timing', [StudentController::class, 'updateTiming'])->name('updateTiming');
    Route::post('/update-doing-exam', [StudentController::class, 'updateDoingExam'])->name('updateDoingExam');
    Route::post('/reset-doing-exam', [StudentController::class, 'resetDoingExam'])->name('resetDoingExam');
    Route::post('/get-practice', [StudentController::class, 'getPractice'])->name('getPractice');
    Route::post('/accpet-exam', [StudentController::class, 'accpectExam'])->name('accpectExam');
    Route::post('/accpet-practice', [StudentController::class, 'acceptPractice'])->name('acceptPractice');

    //học sinh luyện đề
    Route::group(['prefix' => '/luyende'], function () {
        Route::get('/get', [HSLuyenDeController::class, 'list'])->name('list');
        Route::post('/create', [HSLuyenDeController::class, 'luyenDe'])->name('luyenDe');
        Route::put('/update', [HSLuyenDeController::class, 'nopBai'])->name('nopBai');
    });

});

Route::group(['prefix' => '/teacher', 'middleware' => 'teacher'], function () {
    Route::get('/get',     [AdminTeacherController::class, 'getTeacher'])->name('getTeacher');
    //Profile
    Route::put('/update-profile',      [ProfileController::class, 'updateProfileTeacher'])->name('updateProfileTeacher');
    // Route::put('/update-profile',      [ProfileController::class, 'updateProfile'])->name('updateProfile');

    Route::group(['prefix' => '/question'], function () {
        Route::post('/create',        [TeacherConTroller::class, 'addQuestion'])->name('addQuestion');
        Route::post('/file',      [TeacherConTroller::class, 'addFileQuestion'])->name('addFileQuestion');
        // Route::post('/delete-question',      [TeacherConTroller::class, 'destroyQuestion'])->name('destroyQuestion');
        Route::delete('/delete/{question_id}', [TeacherConTroller::class, 'destroyQuestion'])->name('destroyQuestion');
        Route::put('/update/{question_id}', [TeacherConTroller::class, 'updateQuestion'])->name('updateQuestion');
        Route::post('/multi-delete-question', [TeacherConTroller::class, 'multiDeleteQuestion'])->name('multiDeleteQuestion');
    });
    Route::group(['prefix' => '/score'], function () {
        Route::post('/list',        [TeacherConTroller::class, 'listScore'])->name('listScore');
        Route::post('/export',      [TeacherConTroller::class, 'exportScore'])->name('exportScore');
    });
    Route::post('/check-add-question-via-file', [AdminTeacherController::class, 'checkAddQuestionViaFile'])->name('admin.check_add_question_via_file');
    Route::post('/create', [AdminTeacherController::class, 'checkAddQuestions'])->name('checkAddQuestion');
});

Route::group(['prefix' => '/TBM', 'middleware' => 'head_subject'], function () {
    //Profile
    Route::put('/update-profile',      [ProfileController::class, 'updateProfileSubjectHead'])->name('updateProfileSubjectHead');
    // Route::put('/update-profile',      [ProfileController::class, 'updateProfile'])->name('updateProfile');

    Route::get('/', [AdminTBMonController::class, 'index'])->name('index');

    //duyệt đề thi
    Route::post('/', [TBMDuyetDeThiConTroller::class, 'duyetDT'])->name('duyetDT');
    Route::put('/', [TBMDuyetDeThiConTroller::class, 'khongDuyetDT'])->name('khongDuyetDT');
});

