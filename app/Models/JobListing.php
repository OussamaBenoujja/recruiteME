<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobListing extends Model
{
   // In JobListing.php
public function user()
{
    return $this->belongsTo(User::class);
}

public function applications()
{
    return $this->hasMany(Application::class);
}
}
