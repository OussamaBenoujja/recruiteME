<?php

namespace App\Policies;

use App\Models\JobListing;
use App\Models\User;

class JobListingPolicy
{
    /**
     * Determine whether the user can view any job listings.
     */
    public function viewAny(?User $user): bool
    {
        // Anyone can view job listings (even guests)
        return true;
    }

    /**
     * Determine whether the user can view the job listing.
     */
    public function view(?User $user, JobListing $jobListing): bool
    {
        // Anyone can view a job listing
        return true;
    }

    /**
     * Determine whether the user can create job listings.
     */
    public function create(User $user): bool
    {
        return $user->role === 'recruiter' || $user->role === 'admin';
    }

    /**
     * Determine whether the user can update the job listing.
     */
    public function update(User $user, JobListing $jobListing): bool
    {
        // Only the owner or admin can update job listings
        return $user->id === $jobListing->user_id || $user->role === 'admin';
    }

    /**
     * Determine whether the user can delete the job listing.
     */
    public function delete(User $user, JobListing $jobListing): bool
    {
        // Only the owner or admin can delete job listings
        return $user->id === $jobListing->user_id || $user->role === 'admin';
    }
}