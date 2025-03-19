<?php

namespace App\Repositories\Interfaces;

interface ApplicationRepositoryInterface extends RepositoryInterface
{
    public function findByCandidate($candidateId);
    public function findByJobListing($jobListingId);
    public function updateStatus($id, $status);
}