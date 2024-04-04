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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
//
Route::get('/admin/login', [Admincontroller::class, 'indexLogin']);

Route::post('/admin/logout', [Admincontroller::class, 'logout'])->name('logout');
Route::post('/admin/submit-login', [AdminController::class, 'submitLogin']);

// quản lý admin
//'middleware' => 'checkLoginAdmin'
Route::group(['prefix' => '/admin'], function () {

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
        Route::get('/', [AdminMonHocController::class, 'index'])->name('index');
        Route::post('/update-mon', [AdminMonHocController::class, 'updateMon'])->name('updateMon');
        Route::delete('/delete-mon', [AdminMonHocController::class, 'deleteMon'])->name('deleteMon');
        Route::post('/create-mon', [AdminMonHocController::class, 'createMon'])->name('createMon');
    });

});
