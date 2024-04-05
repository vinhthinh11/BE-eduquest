<?php

use App\Http\Controllers\AdminClassController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admincontroller;
use App\Http\Controllers\AdminTBMonController;
use App\Http\Controllers\AdminMonHocController;
use App\Http\Controllers\AdminProfileController;
use App\Http\Controllers\AdminTeacherController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
//

// quản lý admin
Route::get('/admin/login', [Admincontroller::class, 'indexLogin']);
Route::post('/admin/logout', [Admincontroller::class, 'logout'])->name('logout');
Route::post('/admin/submit-login', [AdminController::class, 'submitLogin']);

//Profile
Route::group(['prefix' => 'profiles'], function () {
    Route::get('/',                     [AdminProfileController::class, 'getProfiles'])->name('getProfiles');
    Route::post('/update-profile',     [AdminProfileController::class, 'updateProfile'])->name('updateProfile');
    Route::post('/update-last-login',  [AdminProfileController::class, 'updateLastLogin'])->name('updateLastLogin');
    Route::post('/update-avatar',      [AdminProfileController::class, 'updateAvatar'])->name('updateAvatarProfile');
    Route::post('/admin-info',         [AdminProfileController::class, 'adminInfo'])->name('AdminInfo');
});

Route::group(['prefix' => '/admin'], function () { //, 'middleware' => 'checkLoginAdmin'
    Route::get('/', function () {
        return view('welcome');
    });
    Route::get('/get', [Admincontroller::class, 'getAdmin'])->name('getAdmin');
    Route::post('/update-admin', [Admincontroller::class, 'updateAdmin'])->name('updateAdmin');
    Route::post('/check-add-admin-via-file', [AdminController::class, 'check_add_admin_via_file'])->name('admin.check_add_admin_via_file');
    Route::get('/index', [AdminController::class, 'indexAdmin']);
    Route::post('/create-admin', [AdminController::class, 'createAdmin'])->name('createAdmin');
    Route::delete('/delete-admin', [AdminController::class, 'deleteAdmin'])->name('deleteAdmin');
    Route::put('/update-admin', [AdminController::class, 'updateAdmin'])->name('updateAdmin');
    Route::post('/check-add-question-via-file', [AdminController::class, 'checkAddQuestionViaFile'])->name('admin.check_add_question_via_file');
    Route::get('/question', function () {
        return view('admin.test_question');
    });
    Route::get('/get-questions', [Admincontroller::class, 'getQuestion'])->name('getQuestion');

    Route::group(['prefix' => 'teacher'], function () {
        Route::get('/get',     [AdminTeacherController::class, 'getTeacher'])->name('getTeacher');
        Route::post('/delete', [AdminTeacherController::class, 'destroy'])->name('destroyTeacher');
        Route::post('/update', [AdminTeacherController::class, 'update'])->name('updateTeacher');
        Route::post('/create', [AdminTeacherController::class, 'create'])->name('createTeacher');
        Route::post('/search', [AdminTeacherController::class, 'search'])->name('searchTeacher');
        Route::post('/check-add-teacher-via-file', [AdminTeacherController::class, 'createFileTeacher'])->name('check_add_teacher_via_file');
    });

    Route::group(['prefix' => 'classes'], function () {
        Route::get('/get',      [AdminClassController::class, 'getClasses'])->name('getClasses');
        Route::post('/delete', [AdminClassController::class, 'destroy'])->name('destroyClass');
        Route::post('/update', [AdminClassController::class, 'update'])->name('updateClass');
        Route::post('/create', [AdminClassController::class, 'create'])->name('createClass');
        Route::post('/search', [AdminClassController::class, 'search'])->name('searchClass');
    });


});
