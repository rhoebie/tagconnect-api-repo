<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\NewsController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\AnalyticController;
use App\Http\Controllers\Api\BarangayController;
use App\Http\Controllers\Api\ModeratorController;
use App\Http\Controllers\Api\NotificationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::name('api') // Protected
//     ->group(function () {
//         Route::post('users', [UserController::class, 'store']);
//     });
// routes/api.php
// routes/api.php or routes/web.php (depending on your needs)

// Authentication Controller
Route::post('auth-register-user', [AuthController::class, 'registerUser']);
Route::post('auth-verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('auth-login-user', [AuthController::class, 'login']);
Route::post('auth-logout-user', [AuthController::class, 'logout']);
Route::post('auth-request-otp', [AuthController::class, 'requestOtp']);
Route::post('auth-reset-password', [AuthController::class, 'resetPassword']);
Route::patch('auth-change-password', [AuthController::class, 'changePassword'])->middleware('auth:sanctum');

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('user-news', [NewsController::class, 'getNews']);
    Route::get('user-barangay', [UserController::class, 'userGetBarangay']);
    Route::post('user-feed-reports', [UserController::class, 'userGetFeedReports']);
    Route::get('user-reports', [UserController::class, 'userGetReports']);
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('moderator-update-resolved', [ModeratorController::class, 'moderatorResolved']);
    Route::post('moderator-update-process', [ModeratorController::class, 'moderatorProcess']);
    Route::get('moderator-brgy-info', [ModeratorController::class, 'moderatorBrgyInfo']);
    Route::get('moderator-get-users', [ModeratorController::class, 'moderatorUsers']);
    Route::get('moderator-get-reports', [ModeratorController::class, 'moderatorAllReports']);
    Route::get('moderator-report-types', [ModeratorController::class, 'moderatorReportTypes']);
    Route::post('moderator-yearly-report', [ModeratorController::class, 'moderatorYearlyReport']);
    Route::post('moderator-monthly-report', [ModeratorController::class, 'moderatorMonthlyReport']);
    Route::get('moderator-weekly-report', [ModeratorController::class, 'moderatorweeklyReport']);
});

Route::post('send-notif', [NotificationController::class, 'sendNotification']);

// General Controller
Route::name('api')
    ->middleware('auth:sanctum')
    ->group(function () {
        Route::apiResource('users', UserController::class);
        Route::apiResource('roles', RoleController::class);
        Route::apiResource('reports', ReportController::class);
        Route::apiResource('barangays', BarangayController::class);
    });