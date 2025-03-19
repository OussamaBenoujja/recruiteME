<?php

namespace App\Repositories\Eloquent;

use App\Models\Application;
use App\Repositories\Interfaces\ApplicationRepositoryInterface;

class ApplicationRepository extends BaseRepository implements ApplicationRepositoryInterface
{
    public function __construct(Application $model)
    {
        parent::__construct($model);
    }

    public function findByCandidate($candidateId)
    {
        return $this->model->where('user_id', $candidateId)
            ->with('jobListing')
            ->get();
    }

    public function findByJobListing($jobListingId)
    {
        return $this->model->where('job_listing_id', $jobListingId)
            ->with('user')
            ->get();
    }

    public function updateStatus($id, $status)
    {
        $application = $this->find($id);
        $application->status = $status;
        $application->save();
        
        return $application;
    }
}