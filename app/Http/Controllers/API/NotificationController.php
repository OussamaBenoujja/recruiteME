<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function notifyApplicationStatus(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $notification = Notification::create([
            'user_id' => $id,
            'type' => 'application_status',
            'notifiable_type' => 'Application',
            'notifiable_id' => $request->application_id,
            'message' => $request->message,
        ]);

        // In a real application, you would also send an email here

        return response()->json([
            'status' => 'success',
            'message' => 'Notification sent successfully',
            'data' => $notification,
        ], 201);
    }
}