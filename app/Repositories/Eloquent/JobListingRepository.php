<?php

namespace App\Repositories\Eloquent;

use App\Models\JobListing;
use App\Repositories\Interfaces\JobListingRepositoryInterface;

class JobListingRepository extends BaseRepository implements JobListingRepositoryInterface
{
    public function __construct(JobListing $model)
    {
        parent::__construct($model);
    }

    public function findByRecruiter($recruiterId)
    {
        return $this->model->where('user_id', $recruiterId)->get();
    }

    public function search(array $filters)
    {
        $query = $this->model->query();

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('description', 'like', '%' . $filters['search'] . '%');
            });
        }

        if (isset($filters['location'])) {
            $query->where('location', $filters['location']);
        }

        if (isset($filters['employment_type'])) {
            $query->where('employment_type', $filters['employment_type']);
        }

        $query->where('is_active', $filters['is_active'] ?? true);

        return $query->paginate($filters['per_page'] ?? 15);
    }
}