<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJobListingRequest;
use App\Http\Requests\UpdateJobListingRequest;
use App\Models\JobListing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class JobListingController extends Controller
{
    public function index(Request $request)
    {
        // Anyone can view job listings, no auth check needed
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
        // Check authorization using policy
        $this->authorize('create', JobListing::class);

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

        // Anyone can view a job listing, no auth check needed
        return response()->json([
            'status' => 'success',
            'data' => $jobListing,
        ]);
    }

    public function update(UpdateJobListingRequest $request, $id)
    {
        $jobListing = JobListing::findOrFail($id);

       
        $this->authorize('update', $jobListing);

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

        // Check authorization using policy
        $this->authorize('delete', $jobListing);

        $jobListing->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Job listing deleted successfully',
        ]);
    }
}