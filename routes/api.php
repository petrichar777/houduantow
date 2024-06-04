<?php

use App\Http\Controllers\WwjController;
use App\Http\Controllers\YyhController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::prefix('student')->group(function () {
    // 学生登录
    Route::post('login', [WwjController::class, 'login']);
    // 学生注册
    Route::post('register', [WwjController::class, 'register']);
    // 学生登出
    Route::post('logout', [WwjController::class, 'logout']);
    // 学生报名参加比赛
    Route::post('signup', [WwjController::class, 'student_signup']);
    // 学生删除信息
    Route::delete('delete', [WwjController::class, 'SxqstudentDelete']);
    // 学生查看信息
    Route::post('select', [WwjController::class, 'selectStudent']);
});

Route::prefix('admin')->group(function () {
    // 管理员登录
    Route::post('login', [WwjController::class, 'adminlogin']);
    // 管理员登出
    Route::post('logout', [WwjController::class, 'logout']);
    // 管理员查看信息
    Route::post('select', [WwjController::class, 'selectAdmin']);
    // 管理员查看单个学生报名信息
    Route::post('selectonestu', [WwjController::class, 'selectonestu']);
    // 管理员增加参赛学生
    Route::post('signup', [WwjController::class, 'Admin_signup']);
    // 管理员修改参赛学生信息
    Route::post('rework', [WwjController::class, 'SxqreworkStudent']);
    // 管理员删除学生信息
    Route::delete('delete', [WwjController::class, 'SxqAdminDelete']);
});




