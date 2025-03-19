<?php

namespace App\Services;

use App\Repositories\Interfaces\JobListingRepositoryInterface;

class JobListingService
{
    protected $jobListingRepository;

    public function __construct(JobListingRepositoryInterface $jobListingRepository)
    {
        $this->jobListingRepository = $jobListingRepository;
    }

    public function getAllJobListings(array $filters = [])
    {
        return $this->jobListingRepository->search($filters);
    }

    public function createJobListing(array $data)
    {
        return $this->jobListingRepository->create($data);
    }

    public function updateJobListing($id, array $data)
    {
        return $this->jobListingRepository->update($id, $data);
    }

    public function deleteJobListing($id)
    {
        return $this->jobListingRepository->delete($id);
    }

    public function getJobListingById($id)
    {
        return $this->jobListingRepository->find($id);
    }

    public function getRecruiterJobListings($recruiterId)
    {
        return $this->jobListingRepository->findByRecruiter($recruiterId);
    }
}