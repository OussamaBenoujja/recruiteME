<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobListing extends Model
{

    
protected $fillable = [
    'user_id',
    'title',
    'description',
    'location',
    'company',
    'employment_type',
    'experience_level',
    'salary_min',
    'salary_max',
    'is_active',
    'closing_date'
];
   
public function user()
{
    return $this->belongsTo(User::class);
}

public function applications()
{
    return $this->hasMany(Application::class);
}
}
