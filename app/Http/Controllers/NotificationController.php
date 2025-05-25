<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\NotificationService;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Get notifications data for AJAX
     */
    public function getNotifications()
    {
        $data = $this->notificationService->getNotificationData();
        return response()->json($data);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Request $request)
    {
        $notificationId = $request->input('notification_id');
        $notification = $this->notificationService->markAsRead($notificationId);
        
        if ($notification) {
            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read'
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Notification not found'
        ], 404);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        $count = $this->notificationService->markAllAsRead();
        
        return response()->json([
            'success' => true,
            'message' => "Marked {$count} notifications as read"
        ]);
    }

    /**
     * Get unread count
     */
    public function getUnreadCount()
    {
        $count = $this->notificationService->getUnreadCount();
        
        return response()->json([
            'unread_count' => $count
        ]);
    }
}
