<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Get user notifications
     */
    public function index(Request $request)
    {
        // Check authorization using policy
        $this->authorize('viewAny', Notification::class);

        $perPage = $request->input('per_page', 20);
        $notifications = $this->notificationService->getUserNotifications(
            Auth::id(), 
            $perPage
        );

        return response()->json([
            'status' => 'success',
            'data' => $notifications,
            'unread_count' => $this->notificationService->countUnreadNotifications(Auth::id())
        ]);
    }

    /**
     * Mark a specific notification as read
     */
    public function markAsRead($notificationId)
    {
        $notification = Notification::findOrFail($notificationId);
        
        // Check authorization using policy
        $this->authorize('update', $notification);

        try {
            $result = $this->notificationService->markNotificationAsRead($notificationId);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Notification marked as read',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to mark notification as read',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get unread notifications count
     */
    public function unreadCount()
    {
        // Check authorization using policy
        $this->authorize('viewAny', Notification::class);

        $unreadCount = $this->notificationService->countUnreadNotifications(Auth::id());
        
        return response()->json([
            'status' => 'success',
            'data' => [
                'unread_count' => $unreadCount
            ]
        ]);
    }
}