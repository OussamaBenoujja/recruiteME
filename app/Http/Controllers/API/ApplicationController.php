<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreApplicationRequest;
use App\Models\Application;
use App\Models\JobListing;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ApplicationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware('role:recruiter')->only(['index', 'show']);
        $this->middleware('role:candidate')->only(['myApplications', 'store', 'destroy']);
    }

    public function index(Request $request)
    {
        // For recruiters to list applications for their job listings
        $applications = Application::whereHas('jobListing', function ($query) {
                $query->where('user_id', Auth::id());
            })
            ->when($request->has('job_listing_id'), function ($query) use ($request) {
                return $query->where('job_listing_id', $request->job_listing_id);
            })
            ->when($request->has('status'), function ($query) use ($request) {
                return $query->where('status', $request->status);
            })
            ->with(['user', 'jobListing'])
            ->paginate($request->per_page ?? 15);

        return response()->json([
            'status' => 'success',
            'data' => $applications,
        ]);
    }

    public function myApplications(Request $request)
    {
        // For candidates to list their applications
        $applications = Application::where('user_id', Auth::id())
            ->when($request->has('status'), function ($query) use ($request) {
                return $query->where('status', $request->status);
            })
            ->with('jobListing')
            ->paginate($request->per_page ?? 15);

        return response()->json([
            'status' => 'success',
            'data' => $applications,
        ]);
    }

    public function store(StoreApplicationRequest $request)
    {
        // Check if job listing exists and is active
        $jobListing = JobListing::findOrFail($request->job_listing_id);
        
        if (!$jobListing->is_active) {
            return response()->json([
                'status' => 'error',
                'message' => 'This job listing is no longer active',
            ], 400);
        }

        // Check if user already applied to this job
        $existingApplication = Application::where('user_id', Auth::id())
            ->where('job_listing_id', $request->job_listing_id)
            ->first();

        if ($existingApplication) {
            return response()->json([
                'status' => 'error',
                'message' => 'You have already applied to this job',
            ], 400);
        }

        // Store CV
        $cvPath = $request->file('cv')->store('applications/cv', 'public');
        
        // Store cover letter
        $coverLetterPath = $request->file('cover_letter')->store('applications/cover_letters', 'public');

        // Create application
        $application = Application::create([
            'user_id' => Auth::id(),
            'job_listing_id' => $request->job_listing_id,
            'cv_path' => $cvPath,
            'cover_letter_path' => $coverLetterPath,
            'notes' => $request->notes,
        ]);

        // Create notification for recruiter
        Notification::create([
            'user_id' => $jobListing->user_id,
            'type' => 'new_application',
            'notifiable_type' => Application::class,
            'notifiable_id' => $application->id,
            'message' => 'New application received for ' . $jobListing->title,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Application submitted successfully',
            'data' => $application,
        ], 201);
    }

    public function show($id)
    {
        $application = Application::with(['user', 'jobListing'])->findOrFail($id);

        // Check if user is authorized to view this application
        $authorized = false;
        
        if (Auth::user()->role === 'recruiter') {
            $authorized = $application->jobListing->user_id === Auth::id();
        } elseif (Auth::user()->role === 'candidate') {
            $authorized = $application->user_id === Auth::id();
        }

        if (!$authorized) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'data' => $application,
        ]);
    }

    public function destroy($id)
    {
        $application = Application::findOrFail($id);

        // Check if user is the owner of the application
        if ($application->user_id !== Auth::id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 403);
        }

        // Delete files
        Storage::disk('public')->delete($application->cv_path);
        Storage::disk('public')->delete($application->cover_letter_path);

        $application->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Application withdrawn successfully',
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string|in:pending,reviewing,interviewed,accepted,rejected',
        ]);

        $application = Application::findOrFail($id);

        // Check if user is the recruiter for this job listing
        if (Auth::user()->role !== 'recruiter' || $application->jobListing->user_id !== Auth::id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 403);
        }

        $oldStatus = $application->status;
        $application->status = $request->status;
        $application->save();

        // Create notification for candidate
        Notification::create([
            'user_id' => $application->user_id,
            'type' => 'status_update',
            'notifiable_type' => Application::class,
            'notifiable_id' => $application->id,
            'message' => 'Your application status has been updated from ' . $oldStatus . ' to ' . $request->status,
        ]);

        // In a real app, you would also send an email notification here

        return response()->json([
            'status' => 'success',
            'message' => 'Application status updated successfully',
            'data' => $application,
        ]);
    }

    public function trackStatus($id)
    {
        $application = Application::findOrFail($id);

        // Check if user is the owner of the application
        if ($application->user_id !== Auth::id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'status' => $application->status,
                'updated_at' => $application->updated_at,
            ],
        ]);
    }
}