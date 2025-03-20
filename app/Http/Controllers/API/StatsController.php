<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\JobListing;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class StatsController extends Controller
{
    public function recruiterStats()
    {
        // Check authorization using gate
        if (!Gate::allows('view-recruiter-stats')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 403);
        }

        $userId = Auth::id();

        // Job listing stats
        $jobListingStats = [
            'total' => JobListing::where('user_id', $userId)->count(),
            'active' => JobListing::where('user_id', $userId)->where('is_active', true)->count(),
            'inactive' => JobListing::where('user_id', $userId)->where('is_active', false)->count(),
        ];

        // Application stats
        $applicationStats = [
            'total' => Application::whereHas('jobListing', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })->count(),
            'by_status' => Application::whereHas('jobListing', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status'),
        ];

        // Most popular job listings
        $popularJobListings = JobListing::where('user_id', $userId)
            ->withCount('applications')
            ->orderBy('applications_count', 'desc')
            ->limit(5)
            ->get(['id', 'title', 'applications_count']);

        return response()->json([
            'status' => 'success',
            'data' => [
                'job_listings' => $jobListingStats,
                'applications' => $applicationStats,
                'popular_job_listings' => $popularJobListings,
            ],
        ]);
    }

    public function globalStats()
    {
        // Check authorization using gate
        if (!Gate::allows('view-global-stats')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 403);
        }

        // User stats
        $userStats = [
            'total' => User::count(),
            'by_role' => User::select('role', DB::raw('count(*) as count'))
                ->groupBy('role')
                ->get()
                ->pluck('count', 'role'),
        ];

        // Job listing stats
        $jobListingStats = [
            'total' => JobListing::count(),
            'active' => JobListing::where('is_active', true)->count(),
            'by_month' => JobListing::select(
                    DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                    DB::raw('count(*) as count')
                )
                ->groupBy('month')
                ->orderBy('month')
                ->get()
                ->pluck('count', 'month'),
        ];

        // Application stats
        $applicationStats = [
            'total' => Application::count(),
            'by_status' => Application::select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get()
                ->pluck('count', 'status'),
            'by_month' => Application::select(
                    DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                    DB::raw('count(*) as count')
                )
                ->groupBy('month')
                ->orderBy('month')
                ->get()
                ->pluck('count', 'month'),
        ];

        return response()->json([
            'status' => 'success',
            'data' => [
                'users' => $userStats,
                'job_listings' => $jobListingStats,
                'applications' => $applicationStats,
            ],
        ]);
    } // <-- This closes the globalStats method
} // <-- This closes the StatsController class
