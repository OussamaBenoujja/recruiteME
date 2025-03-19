<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    // For Notification.php
protected $fillable = [
    'user_id',
    'type',
    'notifiable_type',
    'notifiable_id',
    'message',
    'read'
];
}
