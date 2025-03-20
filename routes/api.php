<?php

use App\Http\Controllers\API\ApplicationController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\JobListingController;
use App\Http\Controllers\API\NotificationController;
use App\Http\Controllers\API\StatsController;
use App\Http\Controllers\API\UserController;
use Illuminate\Support\Facades\Route;

// Authentication Routes
Route::group(['prefix' => 'auth'], function () {
    // Public auth routes
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('password/forgot', [AuthController::class, 'forgotPassword']);
    Route::post('password/reset', [AuthController::class, 'resetPassword']);
    
    // Protected auth routes
    Route::middleware('auth:api')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
    });
});

// Job Listings Routes
Route::get('job-listings', [JobListingController::class, 'index']);
Route::get('job-listings/{id}', [JobListingController::class, 'show']);

// Protected Job Listing Routes
Route::middleware('auth:api')->group(function () {
    Route::middleware('role:recruiter')->group(function () {
        Route::post('job-listings', [JobListingController::class, 'store']);
        Route::put('job-listings/{id}', [JobListingController::class, 'update']);
        Route::delete('job-listings/{id}', [JobListingController::class, 'destroy']);
    });
});

// Applications Routes
Route::middleware('auth:api')->group(function () {
    // Recruiter only routes
    Route::middleware('role:recruiter')->group(function () {
        Route::get('applications', [ApplicationController::class, 'index']);
        Route::get('applications/{id}', [ApplicationController::class, 'show']);
        Route::put('applications/{id}/status', [ApplicationController::class, 'updateStatus']);
    });
    
    // Candidate only routes
    Route::middleware('role:candidate')->group(function () {
        Route::get('applications/my', [ApplicationController::class, 'myApplications']);
        Route::post('applications', [ApplicationController::class, 'store']);
        Route::delete('applications/{id}', [ApplicationController::class, 'destroy']);
        Route::get('applications/{id}/status', [ApplicationController::class, 'trackStatus']);
    });
});

// Notifications Routes
Route::middleware('auth:api')->group(function () {
    Route::get('notifications', [NotificationController::class, 'index']);
    Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::put('notifications/{id}/read', [NotificationController::class, 'markAsRead']);
});

// User Routes
Route::middleware('auth:api')->group(function () {
    Route::get('users/profile', [UserController::class, 'profile']);
    Route::put('users/profile', [UserController::class, 'update']);
    
    // Admin only routes
    Route::middleware('role:admin')->group(function () {
        Route::delete('users/{id}', [UserController::class, 'destroy']);
    });
});

// Stats Routes
Route::middleware('auth:api')->group(function () {
    Route::middleware('role:recruiter')->group(function () {
        Route::get('stats/recruiter', [StatsController::class, 'recruiterStats']);
    });
    
    Route::middleware('role:admin')->group(function () {
        Route::get('stats/global', [StatsController::class, 'globalStats']);
    });
});