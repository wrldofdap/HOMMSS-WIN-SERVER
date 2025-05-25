<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'title',
        'message',
        'data',
        'is_read',
        'read_at'
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Scope for unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope for recent notifications
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', Carbon::now()->subDays($days));
    }

    /**
     * Mark notification as read
     */
    public function markAsRead()
    {
        $this->update([
            'is_read' => true,
            'read_at' => Carbon::now()
        ]);
    }

    /**
     * Get formatted time ago
     */
    public function getTimeAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Create a new order notification
     */
    public static function createOrderNotification($order)
    {
        return self::create([
            'type' => 'new_order',
            'title' => 'New Order Received',
            'message' => "New order #{$order->id} from {$order->name} - ₱{$order->total}",
            'data' => [
                'order_id' => $order->id,
                'customer_name' => $order->name,
                'total' => $order->total,
                'url' => route('admin.order.details', ['order_id' => $order->id])
            ]
        ]);
    }

    /**
     * Create order status change notification
     */
    public static function createOrderStatusNotification($order, $oldStatus, $newStatus)
    {
        $statusMessages = [
            'ordered' => 'placed',
            'processing' => 'is being processed',
            'shipped' => 'has been shipped',
            'delivered' => 'has been delivered',
            'canceled' => 'has been canceled'
        ];

        $message = "Order #{$order->id} " . ($statusMessages[$newStatus] ?? $newStatus);

        return self::create([
            'type' => 'order_status_change',
            'title' => 'Order Status Updated',
            'message' => $message,
            'data' => [
                'order_id' => $order->id,
                'customer_name' => $order->name,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'url' => route('admin.order.details', ['order_id' => $order->id])
            ]
        ]);
    }
}
