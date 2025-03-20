<?php

namespace App\Services;

use App\Mail\ApplicationStatusChanged;
use App\Models\Application;
use App\Models\Notification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Send application status change notification
     */
    public function sendApplicationStatusNotification(
        Application $application, 
        string $oldStatus, 
        string $newStatus
    ): void {
        try {
            // Create database notification
            $this->createDatabaseNotification(
                userId: $application->user_id,
                type: 'application_status_update',
                notifiableType: Application::class,
                notifiableId: $application->id,
                message: $this->formatNotificationMessage($application, $oldStatus, $newStatus)
            );

            // Send email notification
            $this->sendEmailNotification($application, $oldStatus, $newStatus);
        } catch (\Exception $e) {
            Log::error('Notification Error: ' . $e->getMessage());
        }
    }

    /**
     * Create a database notification
     */
    public function createDatabaseNotification(
        int $userId,
        string $type,
        string $notifiableType,
        int $notifiableId,
        string $message
    ): Notification {
        return Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'notifiable_type' => $notifiableType,
            'notifiable_id' => $notifiableId,
            'message' => $message,
            'read' => false
        ]);
    }

    /**
     * Send email notification
     */
    private function sendEmailNotification(
        Application $application, 
        string $oldStatus, 
        string $newStatus
    ): void {
        Mail::to($application->user->email)->send(
            new ApplicationStatusChanged($application, $oldStatus, $newStatus)
        );
    }

    /**
     * Format notification message
     */
    private function formatNotificationMessage(
        Application $application, 
        string $oldStatus, 
        string $newStatus
    ): string {
        return "Your application for {$application->jobListing->title} at {$application->jobListing->company} " .
               "has been updated from {$oldStatus} to {$newStatus}.";
    }

    /**
     * Retrieve user notifications
     */
    public function getUserNotifications(int $userId, int $perPage = 20)
    {
        return Notification::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Mark notification as read
     */
    public function markNotificationAsRead(int $notificationId): bool
    {
        $notification = Notification::findOrFail($notificationId);
        return $notification->update(['read' => true]);
    }

    /**
     * Count unread notifications
     */
    public function countUnreadNotifications(int $userId): int
    {
        return Notification::where('user_id', $userId)
            ->where('read', false)
            ->count();
    }
}