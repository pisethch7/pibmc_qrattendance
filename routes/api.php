<?php

use App\Http\Controllers\Api\AttendanceCheckInController;
use App\Http\Controllers\Api\AttendanceRecordController;
use App\Http\Controllers\Api\AttendanceSessionController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\EnrollmentController;
use Illuminate\Support\Facades\Route;

// Public Auth Endpoints
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Protected Endpoints
Route::middleware('auth:sanctum')->group(function () {
    // Auth & Profile
    Route::get('/auth/user', [AuthController::class, 'user']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Courses
    Route::get('/courses', [CourseController::class, 'index']);
    Route::post('/courses', [CourseController::class, 'store']);
    Route::get('/courses/{course}', [CourseController::class, 'show']);

    // Enrollments
    Route::post('/enrollments', [EnrollmentController::class, 'store']);
    Route::delete('/enrollments/{courseId}/{studentId}', [EnrollmentController::class, 'destroy']);
    Route::get('/students', [EnrollmentController::class, 'students']);

    // Attendance Sessions
    Route::post('/attendance/sessions', [AttendanceSessionController::class, 'store']);
    Route::get('/attendance/sessions/{session}/token', [AttendanceSessionController::class, 'currentToken']);
    Route::post('/attendance/sessions/{session}/close', [AttendanceSessionController::class, 'close']);
    Route::get('/attendance/sessions/{session}/report', [AttendanceSessionController::class, 'report']);

    // Student Check-In & WiFi Verification
    Route::post('/attendance/check-in', [AttendanceCheckInController::class, 'checkIn']);
    Route::get('/attendance/wifi-verify', [AttendanceSessionController::class, 'wifiVerify']);

    // Records & History
    Route::patch('/attendance/records/{record}', [AttendanceRecordController::class, 'update']);
    Route::post('/attendance/sessions/{session}/manual-record', [AttendanceRecordController::class, 'manualRecord']);
    Route::get('/attendance/students/{student}/history', [AttendanceRecordController::class, 'studentHistory']);
});
