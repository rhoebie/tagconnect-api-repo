<?php

use App\Http\Controllers\Api\NewsController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\AnalyticController;
use App\Http\Controllers\Api\BarangayController;

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
    Route::get('user-barangay', [AnalyticController::class, 'getAllBarangay']);
    Route::post('user-feed-reports', [AnalyticController::class, 'getfeedReports']);
    Route::get('user-reports', [AnalyticController::class, 'getUserReports']);
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('moderator-brgy-info', [AnalyticController::class, 'moderatorBrgyInfo']);
    Route::get('moderator-get-users', [AnalyticController::class, 'moderatorUsers']);
    Route::get('moderator-get-reports', [AnalyticController::class, 'moderatorAllReports']);
    Route::get('moderator-report-types', [AnalyticController::class, 'moderatorReportTypes']);
    Route::post('moderator-yearly-report', [AnalyticController::class, 'moderatorYearlyReport']);
    Route::post('moderator-monthly-report', [AnalyticController::class, 'moderatorMonthlyReport']);
    Route::get('moderator-weekly-report', [AnalyticController::class, 'moderatorweeklyReport']);
});

// General Controller
Route::name('api')
    ->middleware('auth:sanctum')
    ->group(function () {
        Route::apiResource('users', UserController::class);
        Route::apiResource('roles', RoleController::class);
        Route::apiResource('reports', ReportController::class);
        Route::apiResource('barangays', BarangayController::class);
    });