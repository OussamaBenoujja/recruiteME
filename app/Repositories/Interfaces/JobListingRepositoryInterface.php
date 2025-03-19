<?php

namespace App\Repositories\Interfaces;

interface JobListingRepositoryInterface extends RepositoryInterface
{
    public function findByRecruiter($recruiterId);
    public function search(array $filters);
}