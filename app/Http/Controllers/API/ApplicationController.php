<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreApplicationRequest;
use App\Models\Application;
use App\Models\JobListing;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class ApplicationController extends Controller
{
    
    public function index(Request $request)
    {
        // Check authorization using policy
        $this->authorize('viewAny', Application::class);

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
        // Check authorization using policy
        $this->authorize('viewOwn', Application::class);

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
        
        $this->authorize('create', Application::class);

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

        
        $application = Application::create([
            'user_id' => Auth::id(),
            'job_listing_id' => $request->job_listing_id,
            'cv_path' => $cvPath,
            'cover_letter_path' => $coverLetterPath,
            'notes' => $request->notes,
        ]);

        
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

        
        $this->authorize('view', $application);

        return response()->json([
            'status' => 'success',
            'data' => $application,
        ]);
    }

    public function destroy($id)
    {
        $application = Application::findOrFail($id);

        // Check authorization using policy
        $this->authorize('delete', $application);

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

        
        $this->authorize('updateStatus', $application);

        $oldStatus = $application->status;
        $application->status = $request->status;
        $application->save();

        
        Notification::create([
            'user_id' => $application->user_id,
            'type' => 'status_update',
            'notifiable_type' => Application::class,
            'notifiable_id' => $application->id,
            'message' => 'Your application status has been updated from ' . $oldStatus . ' to ' . $request->status,
        ]);

        

        return response()->json([
            'status' => 'success',
            'message' => 'Application status updated successfully',
            'data' => $application,
        ]);
    }

    public function trackStatus($id)
    {
        $application = Application::findOrFail($id);

        
        $this->authorize('trackStatus', $application);

        return response()->json([
            'status' => 'success',
            'data' => [
                'status' => $application->status,
                'updated_at' => $application->updated_at,
            ],
        ]);
    }
}