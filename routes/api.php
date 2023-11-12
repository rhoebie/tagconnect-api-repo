<?php

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
Route::post('register-user', [AuthController::class, 'registerUser']);
Route::post('verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('login-user', [AuthController::class, 'login']);
Route::post('logout-user', [AuthController::class, 'logout']);
Route::post('request-otp', [AuthController::class, 'requestOtp']);
Route::post('reset-password', [AuthController::class, 'resetPassword']);
Route::patch('change-password', [AuthController::class, 'changePassword'])->middleware('auth:sanctum');

Route::get('all-reports', [AnalyticController::class, 'getReportsByEmergencyType'])->middleware('auth:sanctum')->middleware('auth:sanctum');
Route::get('user-reports', [AnalyticController::class, 'countUserReport'])->middleware('auth:sanctum');
Route::post('barangay-reports', [AnalyticController::class, 'getEmergencyTypeCountsByBarangay'])->middleware('auth:sanctum');
Route::get('moderator-reports', [AnalyticController::class, 'getReportsByAssignedBarangay'])->middleware('auth:sanctum');
Route::get('average-response', [AnalyticController::class, 'averageResponse'])->middleware('auth:sanctum');
Route::post('recent-incident', [AnalyticController::class, 'latestDoneReport'])->middleware('auth:sanctum');
Route::post('register-moderator', [AuthController::class, 'registerModerator'])->middleware('auth:sanctum');

Route::get('get-news', [AnalyticController::class, 'getNews']);

// General Controller
Route::name('api')
    ->middleware('auth:sanctum')
    ->group(function () {
        Route::apiResource('users', UserController::class);
        Route::apiResource('roles', RoleController::class);
        Route::apiResource('reports', ReportController::class);
        Route::apiResource('barangays', BarangayController::class);
    });