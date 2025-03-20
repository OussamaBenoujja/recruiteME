<?php

namespace App\Policies;

use App\Models\Notification;
use App\Models\User;

class NotificationPolicy
{
    /**
     * Determine whether the user can view any notifications.
     */
    public function viewAny(User $user): bool
    {
        // Users can view their own notifications, admin can view any
        return true;
    }

    /**
     * Determine whether the user can view the notification.
     */
    public function view(User $user, Notification $notification): bool
    {
        // Users can only view their own notifications, admin can view any
        return $user->id === $notification->user_id || $user->role === 'admin';
    }

    /**
     * Determine whether the user can update the notification.
     */
    public function update(User $user, Notification $notification): bool
    {
        // Users can only mark their own notifications as read
        return $user->id === $notification->user_id || $user->role === 'admin';
    }
}