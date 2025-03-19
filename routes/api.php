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
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('password/forgot', [AuthController::class, 'forgotPassword']);
    Route::post('password/reset', [AuthController::class, 'resetPassword']);
});

// Job Listings Routes
Route::get('job-listings', [JobListingController::class, 'index']);
Route::get('job-listings/{id}', [JobListingController::class, 'show']);
Route::post('job-listings', [JobListingController::class, 'store']);
Route::put('job-listings/{id}', [JobListingController::class, 'update']);
Route::delete('job-listings/{id}', [JobListingController::class, 'destroy']);

// Applications Routes
Route::get('applications', [ApplicationController::class, 'index']);
Route::get('applications/my', [ApplicationController::class, 'myApplications']);
Route::post('applications', [ApplicationController::class, 'store']);
Route::get('applications/{id}', [ApplicationController::class, 'show']);
Route::delete('applications/{id}', [ApplicationController::class, 'destroy']);
Route::put('applications/{id}/status', [ApplicationController::class, 'updateStatus']);
Route::get('applications/{id}/status', [ApplicationController::class, 'trackStatus']);

// Notifications Routes
Route::post('notifications/application/{id}', [NotificationController::class, 'notifyApplicationStatus']);

// User Routes
Route::get('users/profile', [UserController::class, 'profile']);
Route::put('users/profile', [UserController::class, 'update']);
Route::delete('users/{id}', [UserController::class, 'destroy']);

// Stats Routes
Route::get('stats/recruiter', [StatsController::class, 'recruiterStats']);
Route::get('stats/global', [StatsController::class, 'globalStats']);