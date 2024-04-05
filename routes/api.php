<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admincontroller;
use App\Http\Controllers\AdminTBMonController;
use App\Http\Controllers\AdminMonHocController;

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
Route::group(['prefix' => '/admin' ,'middleware' => 'checkLoginAdmin'], function () {
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
    Route::get('/question', function () {return view('admin.test_question');});
    Route::get('/get-questions', [Admincontroller::class, 'getQuestion'])->name('getQuestion');


});
//'middleware' => 'checkLoginAdmin'
Route::group(['prefix' => '/admin', 'middleware' => 'checkLoginAdmin'], function () {


    //quản lý trưởng bộ môn
    Route::group(['prefix' => '/truongbomon'], function () {
        Route::get('/', [AdminTBMonController::class, 'index'])->name('index');
        Route::post('/update-tbm', [AdminTBMonController::class, 'updateTBM'])->name('updateTBM');
        Route::post('/check-add-tbm-via-file', [AdminTBMonController::class, 'check_add_tbm_via_file'])->name('check_add_tbm_via_file');
        Route::post('/create-tbm', [AdminTBMonController::class, 'createTBM'])->name('createTBM');
        Route::delete('/delete-tbm', [AdminTBMonController::class, 'deleteTBM'])->name('deleteTBM');
        Route::put('/update-tbm', [AdminTBMonController::class, 'updateTBM'])->name('updateTBM');
    });

    //quản lý môn
    Route::group(['prefix' => '/mon'], function () {
        Route::post('/', [AdminMonHocController::class, 'createMon'])->name('createMon');
        Route::get('/', [AdminMonHocController::class, 'index'])->name('index');
        Route::put('/', [AdminMonHocController::class, 'updateMon'])->name('updateMon');
        Route::delete('/', [AdminMonHocController::class, 'deleteMon'])->name('deleteMon');
    });

});
