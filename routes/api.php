<?php

use App\Http\Controllers\AdminClassController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admincontroller;
use App\Http\Controllers\AdminTBMonController;
use App\Http\Controllers\AdminMonHocController;
use App\Http\Controllers\AdminProfileController;
use App\Http\Controllers\AdminTeacherController;
use App\Http\Controllers\StatistController;
use App\Http\Controllers\TeacherConTroller;
use App\Http\Controllers\TeacherScoreConTroller;
use App\Http\Controllers\AdminHSController;

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
//

// Login
Route::get('/admin/login', [Admincontroller::class, 'indexLogin']);
Route::post('/admin/logout', [Admincontroller::class, 'logout'])->name('logout');
Route::post('/submit-login', [AdminController::class, 'submitLogin']);

Route::group(['prefix' => '/admin', 'middleware' => 'checkLoginAdmin'], function () {
    // API route ----------------------------
    //ql Admin
    Route::get('/', function () {return view('welcome');});
    Route::get('/get', [Admincontroller::class, 'getAdmin'])->name('getAdmin');
    Route::post('/update-admin', [Admincontroller::class, 'updateAdmin'])->name('updateAdmin');
    Route::post('/check-add-admin-via-file', [AdminController::class, 'check_add_admin_via_file'])->name('admin.check_add_admin_via_file');
    Route::get('/index', [AdminController::class, 'indexAdmin']);
    Route::post('/create-admin', [AdminController::class, 'createAdmin'])->name('createAdmin');
    Route::delete('/delete-admin', [AdminController::class, 'deleteAdmin'])->name('deleteAdmin');
    Route::put('/update-admin', [AdminController::class, 'updateAdmin'])->name('updateAdmin');
    Route::post('/check-add-question-via-file', [AdminController::class, 'checkAddQuestionViaFile'])->name('admin.check_add_question_via_file');
    Route::post('/check-add-question', [Admincontroller::class, 'checkAddQuestions'])->name('checkAddQuestion');
    Route::get('/question', function () {return view('admin.test_question');});
    Route::get('/get-questions', [Admincontroller::class, 'getQuestion'])->name('getQuestion');
    Route::get('/get-level', [Admincontroller::class, 'getLevels'])->name('getLevel');
    Route::get('/get-grade', [Admincontroller::class, 'getGrades'])->name('getGrade');
    Route::get('/get-status', [Admincontroller::class, 'getStatus'])->name('getStatus');
    Route::get('/get-subjects', [Admincontroller::class, 'getSubjects'])->name('getSubjects');
    Route::post('/update-questions', [Admincontroller::class, 'updateQuestions'])->name(('updateQuestions'));
    Route::delete('/delete-question', [Admincontroller::class, 'deleteQuestion'])->name(('deleteQuestion'));
    ///
    //Profile
    Route::group(['prefix' => 'profiles'], function () {
        Route::get('/',                    [AdminProfileController::class, 'getProfiles'])->name('getProfiles');
        Route::post('/update-profile',     [AdminProfileController::class, 'updateProfile'])->name('updateProfile');
        Route::post('/update-last-login',  [AdminProfileController::class, 'updateLastLogin'])->name('updateLastLogin');
        Route::post('/update-avatar',      [AdminProfileController::class, 'updateAvatar'])->name('updateAvatarProfile');
        Route::post('/admin-info',         [AdminProfileController::class, 'adminInfo'])->name('adminInfo');
    });

    //Thong Ke
    Route::post('/list-statist', [StatistController::class, 'listStatist'])->name('listStatist');
    Route::post('/list-statis2t-scores', [StatistController::class, 'listStatistScores'])->name('listStatistScores');

    //ql Teacher
    Route::group(['prefix' => 'teacher'], function () {
        Route::get('/get',     [AdminTeacherController::class, 'getTeacher'])->name('getTeacher');
        Route::post('/delete', [AdminTeacherController::class, 'destroy'])->name('destroyTeacher');
        Route::post('/update', [AdminTeacherController::class, 'update'])->name('updateTeacher');
        Route::post('/edit',   [AdminTeacherController::class, 'edit'])->name('editTeacher');
        Route::post('/create', [AdminTeacherController::class, 'create'])->name('createTeacher');
        Route::post('/search', [AdminTeacherController::class, 'search'])->name('searchTeacher');
        Route::post('/delete-check-box', [AdminTeacherController::class, 'deleteCheckbox'])->name('deleteCheckbox');
        Route::post('/check-add-teacher-via-file', [AdminTeacherController::class, 'createFileTeacher'])->name('check_add_teacher_via_file');
    });

    //ql Class
    Route::group(['prefix' => 'classes'], function () {
        Route::get('/get',     [AdminClassController::class, 'getClasses'])->name('getClasses');
        Route::post('/delete', [AdminClassController::class, 'destroy'])->name('destroyClass');
        Route::post('/update', [AdminClassController::class, 'update'])->name('updateClass');
        Route::post('/edit',   [AdminClassController::class, 'edit'])->name('editClass');
        Route::post('/create', [AdminClassController::class, 'create'])->name('createClass');
        Route::post('/search', [AdminClassController::class, 'search'])->name('searchClass');
        Route::post('/delete-check-box', [AdminClassController::class, 'deleteCheckbox'])->name('deleteCheckbox');
    });

    //ql TBM
    Route::group(['prefix' => '/truongbomon'], function () {
        Route::get('/', [AdminTBMonController::class, 'index'])->name('index');
        Route::post('/update-tbm', [AdminTBMonController::class, 'updateTBM'])->name('updateTBM');
        Route::post('/check-add-tbm-via-file', [AdminTBMonController::class, 'check_add_tbm_via_file'])->name('check_add_tbm_via_file');
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
    Route::group(['prefix' => '/hocsinh'], function () {
        Route::get('/', [AdminHSController::class, 'index'])->name('index');
        Route::post('/file-hs', [AdminHSController::class, 'check_add_hs_via_file'])->name('check_add_hs_via_file');
        Route::post('/create', [AdminHSController::class, 'createHS'])->name('createHS');
        Route::delete('/', [AdminHSController::class, 'deleteHS'])->name('deleteHS');
        Route::put('/', [AdminHSController::class, 'updateHS'])->name('updateHS');
    });

    //Teacher controller
    Route::group(['prefix' => '/teacher'], function () {
        Route::group(['prefix' => '/score'], function () {
            Route::post('/list',        [TeacherConTroller::class, 'listScore'])->name('listScore');
            Route::post('/export',      [TeacherConTroller::class, 'exportScore'])->name('exportScore');
        });
    });

});

// Route::group(['prefix' => 'laravel-filemanager'], function () {
//     \UniSharp\LaravelFilemanager\Lfm::routes();
// });
