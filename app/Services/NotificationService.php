<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Order;

class NotificationService
{
    /**
     * Create notification for new order
     */
    public function createNewOrderNotification(Order $order)
    {
        return Notification::createOrderNotification($order);
    }

    /**
     * Create notification for order status change
     */
    public function createOrderStatusNotification(Order $order, $oldStatus, $newStatus)
    {
        return Notification::createOrderStatusNotification($order, $oldStatus, $newStatus);
    }

    /**
     * Get unread notifications count
     */
    public function getUnreadCount()
    {
        return Notification::unread()->count();
    }

    /**
     * Get recent notifications for display
     */
    public function getRecentNotifications($limit = 10)
    {
        return Notification::orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($notificationId)
    {
        $notification = Notification::find($notificationId);
        if ($notification) {
            $notification->markAsRead();
        }
        return $notification;
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        return Notification::unread()->update([
            'is_read' => true,
            'read_at' => now()
        ]);
    }

    /**
     * Delete old notifications (older than 30 days)
     */
    public function cleanupOldNotifications()
    {
        return Notification::where('created_at', '<', now()->subDays(30))->delete();
    }

    /**
     * Get notification data for AJAX requests
     */
    public function getNotificationData()
    {
        $notifications = $this->getRecentNotifications();
        $unreadCount = $this->getUnreadCount();

        return [
            'notifications' => $notifications->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'time_ago' => $notification->time_ago,
                    'is_read' => $notification->is_read,
                    'url' => $notification->data['url'] ?? '#',
                    'icon' => $this->getNotificationIcon($notification->type)
                ];
            }),
            'unread_count' => $unreadCount
        ];
    }

    /**
     * Get icon for notification type
     */
    private function getNotificationIcon($type)
    {
        $icons = [
            'new_order' => 'icon-shopping-bag',
            'order_status_change' => 'icon-truck',
            'low_stock' => 'icon-alert-triangle',
            'system' => 'icon-info'
        ];

        return $icons[$type] ?? 'icon-bell';
    }
}
