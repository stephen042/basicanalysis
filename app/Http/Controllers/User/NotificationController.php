<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Get user notifications for AJAX requests
     */
    public function index(Request $request)
    {
        try {
            $userId = Auth::id();
            $limit = $request->get('limit', 10);
            $unreadOnly = $request->get('unread_only', false);

            $notifications = $this->notificationService->getUserNotifications($userId, $limit, $unreadOnly);
            $unreadCount = $this->notificationService->getUnreadCount($userId);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'notifications' => $notifications->items(),
                    'unread_count' => $unreadCount,
                    'pagination' => [
                        'current_page' => $notifications->currentPage(),
                        'last_page' => $notifications->lastPage(),
                        'per_page' => $notifications->perPage(),
                        'total' => $notifications->total(),
                    ]
                ]);
            }

            return view('user.notifications.index', compact('notifications', 'unreadCount'));
        } catch (\Exception $e) {
            Log::error('Error loading notifications', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'notifications' => [],
                    'unread_count' => 0,
                    'error' => 'Failed to load notifications'
                ], 500);
            }

            return view('user.notifications.index', [
                'notifications' => collect(),
                'unreadCount' => 0
            ]);
        }
    }

    /**
     * Get notification dropdown content
     */
    public function dropdown()
    {
        try {
            $userId = Auth::id();
            
            // Get notifications directly from the database using DB query
            $notifications = \DB::table('notifications')
                ->where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
            
            // Get unread count
            $unreadCount = \DB::table('notifications')
                ->where('user_id', $userId)
                ->whereNull('read_at')
                ->count();

            // Transform notifications for JSON response
            $notificationsData = $notifications->map(function ($notification) {
                $createdAt = \Carbon\Carbon::parse($notification->created_at);
                return [
                    'id' => $notification->id,
                    'title' => $notification->title ?? 'Notification',
                    'message' => $notification->message ?? '',
                    'icon' => $notification->icon ?? 'fas fa-bell',
                    'priority' => $notification->priority ?? 'normal',
                    'read_at' => $notification->read_at,
                    'created_at' => $createdAt->toISOString(),
                    'time_ago' => $createdAt->diffForHumans(),
                ];
            })->values();

            return response()->json([
                'notifications' => $notificationsData,
                'unread_count' => $unreadCount
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading notifications dropdown', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);

            return response()->json([
                'notifications' => [],
                'unread_count' => 0
            ]);
        }
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Request $request, $id)
    {
        try {
            $userId = Auth::id();
            $result = $this->notificationService->markAsRead($id, $userId);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => $result,
                    'unread_count' => $this->notificationService->getUnreadCount($userId)
                ]);
            }

            if ($result) {
                return redirect()->back()->with('success', 'Notification marked as read');
            }

            return redirect()->back()->with('error', 'Failed to mark notification as read');
        } catch (\Exception $e) {
            Log::error('Error marking notification as read', [
                'notification_id' => $id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to mark notification as read',
                    'message' => $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Failed to mark notification as read');
        }
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request)
    {
        try {
            $userId = Auth::id();
            $count = $this->notificationService->markAllAsRead($userId);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Marked {$count} notifications as read",
                    'unread_count' => 0
                ]);
            }

            return redirect()->back()->with('success', "Marked {$count} notifications as read");
        } catch (\Exception $e) {
            Log::error('Error marking all notifications as read', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to mark notifications as read',
                    'message' => $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Failed to mark notifications as read');
        }
    }

    /**
     * Delete notification
     */
    public function delete(Request $request, $id)
    {
        try {
            $userId = Auth::id();
            $result = $this->notificationService->deleteNotification($id, $userId);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => (bool) $result,
                    'unread_count' => $this->notificationService->getUnreadCount($userId)
                ]);
            }

            if ($result) {
                return redirect()->back()->with('success', 'Notification deleted');
            }

            return redirect()->back()->with('error', 'Failed to delete notification');
        } catch (\Exception $e) {
            Log::error('Error deleting notification', [
                'notification_id' => $id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to delete notification',
                    'message' => $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Failed to delete notification');
        }
    }

    /**
     * Get unread count
     */
    public function unreadCount()
    {
        $userId = Auth::id();
        $count = $this->notificationService->getUnreadCount($userId);

        return response()->json(['count' => $count]);
    }

    /**
     * Get notification statistics
     */
    public function stats()
    {
        $userId = Auth::id();
        $stats = $this->notificationService->getNotificationStats($userId);

        return response()->json($stats);
    }
}
