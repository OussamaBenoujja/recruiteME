<?php

namespace App\Policies;

use App\Models\Application;
use App\Models\User;

class ApplicationPolicy
{
    /**
     * Determine whether the user can view any applications.
     */
    public function viewAny(User $user): bool
    {
        // Only recruiters and admins can view all applications
        return $user->role === 'recruiter' || $user->role === 'admin';
    }

    /**
     * Determine whether the user can view their own applications.
     */
    public function viewOwn(User $user): bool
    {
        // Candidates can view their own applications
        return $user->role === 'candidate' || $user->role === 'admin';
    }

    /**
     * Determine whether the user can view the application.
     */
    public function view(User $user, Application $application): bool
    {
        // Candidate can view their own application
        if ($user->role === 'candidate') {
            return $user->id === $application->user_id;
        }
        
        // Recruiter can view applications for their job listings
        if ($user->role === 'recruiter') {
            return $user->id === $application->jobListing->user_id;
        }
        
        // Admin can view any application
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can create applications.
     */
    public function create(User $user): bool
    {
        // Only candidates can create applications
        return $user->role === 'candidate' || $user->role === 'admin';
    }

    /**
     * Determine whether the user can update the application status.
     */
    public function updateStatus(User $user, Application $application): bool
    {
        // Only the job listing owner (recruiter) or admin can update the status
        return $user->id === $application->jobListing->user_id || $user->role === 'admin';
    }

    /**
     * Determine whether the user can delete the application.
     */
    public function delete(User $user, Application $application): bool
    {
        // Only the application owner (candidate) or admin can delete the application
        return $user->id === $application->user_id || $user->role === 'admin';
    }

    /**
     * Determine whether the user can track the application status.
     */
    public function trackStatus(User $user, Application $application): bool
    {
        // Only the application owner (candidate) or admin can track the status
        return $user->id === $application->user_id || $user->role === 'admin';
    }
}