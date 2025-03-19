<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Application extends Model
{

    // For Application.php
protected $fillable = [
    'user_id',
    'job_listing_id',
    'status',
    'cv_path',
    'cover_letter_path',
    'notes'
];

    // In Application.php
public function user()
{
    return $this->belongsTo(User::class);
}

public function jobListing()
{
    return $this->belongsTo(JobListing::class);
}

public function notifications()
{
    return $this->morphMany(Notification::class, 'notifiable');
}
}
