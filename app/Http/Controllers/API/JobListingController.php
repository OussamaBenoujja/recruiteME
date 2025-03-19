<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJobListingRequest;
use App\Http\Requests\UpdateJobListingRequest;
use App\Models\JobListing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JobListingController extends Controller
{
  

    public function index(Request $request)
    {
        $jobListings = JobListing::query()
            ->when($request->has('search'), function ($query) use ($request) {
                return $query->where('title', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%');
            })
            ->when($request->has('location'), function ($query) use ($request) {
                return $query->where('location', $request->location);
            })
            ->when($request->has('employment_type'), function ($query) use ($request) {
                return $query->where('employment_type', $request->employment_type);
            })
            ->where('is_active', true)
            ->paginate($request->per_page ?? 15);

        return response()->json([
            'status' => 'success',
            'data' => $jobListings,
        ]);
    }

    public function store(StoreJobListingRequest $request)
    {
        $jobListing = JobListing::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'description' => $request->description,
            'location' => $request->location,
            'company' => $request->company,
            'employment_type' => $request->employment_type,
            'experience_level' => $request->experience_level,
            'salary_min' => $request->salary_min,
            'salary_max' => $request->salary_max,
            'closing_date' => $request->closing_date,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Job listing created successfully',
            'data' => $jobListing,
        ], 201);
    }

    public function show($id)
    {
        $jobListing = JobListing::findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $jobListing,
        ]);
    }

    public function update(UpdateJobListingRequest $request, $id)
    {
        $jobListing = JobListing::findOrFail($id);

        // Check if the user is the owner of the job listing
        if ($jobListing->user_id !== Auth::id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 403);
        }

        $jobListing->update($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Job listing updated successfully',
            'data' => $jobListing,
        ]);
    }

    public function destroy($id)
    {
        $jobListing = JobListing::findOrFail($id);

        // Check if the user is the owner of the job listing
        if ($jobListing->user_id !== Auth::id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 403);
        }

        $jobListing->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Job listing deleted successfully',
        ]);
    }
}