<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
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
