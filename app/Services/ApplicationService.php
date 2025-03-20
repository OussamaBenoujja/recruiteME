<?php

namespace App\Services;

use App\Repositories\Interfaces\ApplicationRepositoryInterface;
use App\Repositories\Interfaces\JobListingRepositoryInterface;
use App\Models\Notification;
use Illuminate\Support\Facades\Storage;
use App\Services\NotificationService;


class ApplicationService
{
    protected $applicationRepository;
    protected $jobListingRepository;

    public function __construct(
        ApplicationRepositoryInterface $applicationRepository,
        JobListingRepositoryInterface $jobListingRepository
    ) {
        $this->applicationRepository = $applicationRepository;
        $this->jobListingRepository = $jobListingRepository;
    }

    public function getCandidateApplications($candidateId)
    {
        return $this->applicationRepository->findByCandidate($candidateId);
    }

    public function getJobApplications($jobListingId)
    {
        return $this->applicationRepository->findByJobListing($jobListingId);
    }

    public function applyForJob(array $data, $cvFile, $coverLetterFile)
    {
        // Store files
        $cvPath = $cvFile->store('applications/cv', 'public');
        $coverLetterPath = $coverLetterFile->store('applications/cover_letters', 'public');

        // Create application
        $applicationData = [
            'user_id' => $data['user_id'],
            'job_listing_id' => $data['job_listing_id'],
            'cv_path' => $cvPath,
            'cover_letter_path' => $coverLetterPath,
            'notes' => $data['notes'] ?? null,
        ];

        $application = $this->applicationRepository->create($applicationData);

        // Create notification for recruiter
        $jobListing = $this->jobListingRepository->find($data['job_listing_id']);
        
        Notification::create([
            'user_id' => $jobListing->user_id,
            'type' => 'new_application',
            'notifiable_type' => 'App\Models\Application',
            'notifiable_id' => $application->id,
            'message' => 'New application received for ' . $jobListing->title,
        ]);

        return $application;
    }

    public function withdrawApplication($id, $userId)
    {
        $application = $this->applicationRepository->find($id);

        // Check if user is the owner of the application
        if ($application->user_id !== $userId) {
            return false;
        }

        // Delete files
        Storage::disk('public')->delete($application->cv_path);
        Storage::disk('public')->delete($application->cover_letter_path);

        return $this->applicationRepository->delete($id);
    }

    public function updateApplicationStatus($id, $status, $recruiterId)
    {
        $application = $this->applicationRepository->find($id);
        $jobListing = $this->jobListingRepository->find($application->job_listing_id);

        // Check if user is the recruiter for this job listing
        if ($jobListing->user_id !== $recruiterId) {
            return false;
        }

        $oldStatus = $application->status;
        $application = $this->applicationRepository->updateStatus($id, $status);

        // Create notification for candidate
        Notification::create([
            'user_id' => $application->user_id,
            'type' => 'status_update',
            'notifiable_type' => 'App\Models\Application',
            'notifiable_id' => $application->id,
            'message' => 'Your application status has been updated from ' . $oldStatus . ' to ' . $status,
        ]);

        return $application;
    }
}