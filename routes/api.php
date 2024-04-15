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

Route::group([

    // 'middleware' => 'api',

], function ($router) {

    Route::post('login', [AuthController::class, 'login']);
    // Route::post('logout', 'AuthController@logout');
    // Route::post('refresh', 'AuthController@refresh');
    Route::post('me', [AuthController::class, 'me']);

});

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
//

// Login
// Route::get('/admin/login', [Admincontroller::class, 'indexLogin']);
// Route::post('/admin/logout', [Admincontroller::class, 'logout'])->name('logout');


// Route::post('/submit-login', [AdminController::class, 'submitLogin']);
// 'middleware' => 'checkLoginAdmin'
Route::group(['prefix' => '/admin','middleware' => 'checkLoginAdmin'], function () {
    // API route ----------------------------
    // this line was add to check if huong could receive the change in his repo

    //ql Admin
    Route::get('/', function () {return view('welcome');});
    Route::get('/get', [Admincontroller::class, 'getAdmin'])->name('getAdmin');
    Route::post('/check-add-admin-via-file', [AdminController::class, 'check_add_admin_via_file'])->name('admin.check_add_admin_via_file');
    // Route::get('/index', [AdminController::class, 'indexAdmin']);
    Route::post('/create', [AdminController::class, 'createAdmin'])->name('createAdmin');
    Route::delete('/delete', [AdminController::class, 'deleteAdmin'])->name('deleteAdmin');
    Route::put('/update', [AdminController::class, 'updateAdmin'])->name('updateAdmin');

    //ql Question
    Route::group(['prefix' => 'question'], function () {
        Route::post('/check-add-question-via-file', [AdminController::class, 'checkAddQuestionViaFile'])->name('admin.check_add_question_via_file');
        Route::post('/check-add-question', [Admincontroller::class, 'checkAddQuestions'])->name('checkAddQuestion');
        Route::get('/get', [Admincontroller::class, 'getQuestion'])->name('getQuestion');
        Route::put('/update', [Admincontroller::class, 'updateQuestions'])->name(('updateQuestions'));
        Route::delete('/delete', [Admincontroller::class, 'deleteQuestion'])->name(('deleteQuestion'));
        Route::get('/get-grade', [Admincontroller::class, 'getGrades'])->name('getGrade');
        Route::get('/get-status', [Admincontroller::class, 'getStatus'])->name('getStatus');
        Route::get('/get-subjects', [Admincontroller::class, 'getSubjects'])->name('getSubjects');
        Route::get('/get-level', [Admincontroller::class, 'getLevels'])->name('getLevel');
        Route::post('/update-questions', [Admincontroller::class, 'updateQuestions'])->name(('updateQuestions'));

        Route::post('check-add-test', [Admincontroller::class, 'checkAddTest'])->name(('checkAddTest'));
 });

    ///
    //Profile
    Route::group(['prefix' => 'profiles'], function () {
        Route::put('/update-profile',      [ProfileController::class, 'updateProfile'])->name('updateProfile');
        Route::post('/update-last-login',  [ProfileController::class, 'updateLastLogin'])->name('updateLastLogin');
        Route::post('/update-avatar',      [ProfileController::class, 'updateAvatar'])->name('updateAvatarProfile');
        Route::post('/admin-info',         [ProfileController::class, 'adminInfo'])->name('adminInfo');
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
        Route::post('/delete', [AdminTeacherController::class, 'destroy'])->name('destroyTeacher');
        Route::put('/update', [AdminTeacherController::class, 'update'])->name('updateTeacher');
        Route::post('/edit',   [AdminTeacherController::class, 'edit'])->name('editTeacher');
        Route::post('/create', [AdminTeacherController::class, 'create'])->name('createTeacher');
        Route::post('/search', [AdminTeacherController::class, 'search'])->name('searchTeacher');
        Route::post('/delete-check-box', [AdminTeacherController::class, 'deleteCh2eckbox'])->name('deleteCheckbox');
        Route::post('/check-add-teacher-via-file', [AdminTeacherController::class, 'createFileTeacher'])->name('check_add_teacher_via_file');
    });

    //ql Class
    Route::group(['prefix' => 'class'], function () {
        Route::get('/get',     [AdminClassController::class, 'getClasses'])->name('getClasses');
        Route::delete('/delete', [AdminClassController::class, 'destroy'])->name('destroyClass');
        Route::put('/update', [AdminClassController::class, 'update'])->name('updateClass');
        Route::put('/edit',   [AdminClassController::class, 'edit'])->name('editClass');
        Route::put('/create', [AdminClassController::class, 'create'])->name('createClass');
        Route::post('/search', [AdminClassController::class, 'search'])->name('searchClass');
        Route::delete('/delete-check-box', [AdminClassController::class, 'deleteCheckbox'])->name('deleteCheckbox');
    });

    //ql TBM
    Route::group(['prefix' => '/truongbomon'], function () {
        Route::get('/', [AdminTBMonController::class, 'index'])->name('index');
        Route::post('/update-tbm', [AdminTBMonController::class, 'updateTBM'])->name('updateTBM');
        Route::post('/file', [AdminTBMonController::class, 'check_add_tbm_via_file'])->name('check_add_tbm_via_file');
        Route::post('/create-tbm', [AdminTBMonController::class, 'createTBM'])->name('createTBM');
        Route::delete('/delete-tbm', [AdminTBMonController::class, 'deleteTBM'])->name('deleteTBM');
        Route::put('/update-tbm', [AdminTBMonController::class, 'updateTBM'])->name('updateTBM');
    });

    //ql Môn học
    Route::group(['prefix' => '/mon'], function () {
        Route::get('/', [AdminMonHocController::class, 'index'])->name('index');
        Route::post('/', [AdminMonHocController::class, 'createMon'])->name('createMon');
        Route::delete('/', [AdminMonHocController::class, 'deleteMon'])->name('deleteMon');
        Route::put('/', [AdminMonHocController::class, 'updateMon'])->name('updateMon');
    });

    //ql học sinh
    Route::group(['prefix' => '/student'], function () {
        Route::get('/get', [AdminHSController::class, 'index'])->name('index');
        Route::post('/create', [AdminHSController::class, 'createHS'])->name('createHS');
        Route::delete('/delete', [AdminHSController::class, 'deleteHS'])->name('deleteHS');
        Route::put('/update', [AdminHSController::class, 'updateHS'])->name('updateHS');
        Route::post('/file', [AdminHSController::class, 'check_add_hs_via_file'])->name('check_add_hs_via_file');
    });

    //Teacher controller
    Route::group(['prefix' => '/teacher'], function () {
        Route::group(['prefix' => '/score'], function () {
            Route::post('/list',        [TeacherConTroller::class, 'listScore'])->name('listScore');
            Route::post('/export',      [TeacherConTroller::class, 'exportScore'])->name('exportScore');
        });
    });

});

