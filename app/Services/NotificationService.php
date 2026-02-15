<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class NotificationService
{
    /**
     * Create a new notification
     */
    public function create($userId, $type, $title, $message, $data = [], $priority = 'normal', $actionUrl = null, $icon = null)
    {
        try {
            return Notification::create([
                'user_id' => $userId,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'data' => $data,
                'priority' => $priority,
                'action_url' => $actionUrl,
                'icon' => $icon ?? $this->getDefaultIcon($type),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create notification: ' . $e->getMessage(), [
                'user_id' => $userId,
                'type' => $type,
                'title' => $title,
            ]);
            return null;
        }
    }

    /**
     * Send welcome notification for new users
     */
    public function sendWelcomeNotification($userId, $userName)
    {
        $title = 'Welcome to the Platform!';
        $message = "Hello {$userName}! Welcome to our trading platform. Your account has been successfully created and you can now start exploring our features.";

        $data = [
            'user_name' => $userName,
            'registration_time' => Carbon::now()->toISOString(),
            'welcome_type' => 'new_user_registration'
        ];

        return $this->create(
            $userId,
            Notification::TYPE_ACCOUNT,
            $title,
            $message,
            $data,
            Notification::PRIORITY_NORMAL,
            route('dashboard'),
            'fas fa-user-plus'
        );
    }

    /**
     * Send admin message notification
     */
    public function sendAdminMessageNotification($userId, $subject, $message, $adminName = 'Admin')
    {
        $title = $subject ?: 'Message from Admin';
        $notificationMessage = "You have received a message from {$adminName}: " . substr($message, 0, 100);

        if (strlen($message) > 100) {
            $notificationMessage .= '...';
        }

        $data = [
            'subject' => $subject,
            'full_message' => $message,
            'admin_name' => $adminName,
            'sent_time' => Carbon::now()->toISOString(),
        ];

        return $this->create(
            $userId,
            Notification::TYPE_SYSTEM,
            $title,
            $notificationMessage,
            $data,
            Notification::PRIORITY_HIGH,
            null,
            'fas fa-envelope'
        );
    }

    /**
     * Send profile update notification
     */
    public function sendProfileUpdateNotification($userId, $updateType, $details = [])
    {
        $title = 'Profile Updated';
        $message = "Your {$updateType} has been updated successfully.";

        $data = array_merge([
            'update_type' => $updateType,
            'updated_time' => Carbon::now()->toISOString(),
        ], $details);

        return $this->create(
            $userId,
            Notification::TYPE_ACCOUNT,
            $title,
            $message,
            $data,
            Notification::PRIORITY_NORMAL,
            route('profile'),
            'fas fa-user-edit'
        );
    }

    /**
     * Send password change notification
     */
    public function sendPasswordChangeNotification($userId)
    {
        $title = 'Password Changed';
        $message = "Your account password has been changed successfully. If this wasn't you, please contact support immediately.";

        $data = [
            'change_type' => 'password',
            'changed_time' => Carbon::now()->toISOString(),
            'security_alert' => true,
        ];

        return $this->create(
            $userId,
            Notification::TYPE_ACCOUNT,
            $title,
            $message,
            $data,
            Notification::PRIORITY_HIGH,
            route('profile'),
            'fas fa-shield-alt'
        );
    }

    /**
     * Send account settings update notification
     */
    public function sendAccountSettingsNotification($userId, $settingsType, $changes = [])
    {
        $title = 'Account Settings Updated';
        $message = "Your {$settingsType} settings have been updated successfully.";

        $data = [
            'settings_type' => $settingsType,
            'changes' => $changes,
            'updated_time' => Carbon::now()->toISOString(),
        ];

        return $this->create(
            $userId,
            Notification::TYPE_ACCOUNT,
            $title,
            $message,
            $data,
            Notification::PRIORITY_NORMAL,
            route('profile'),
            'fas fa-cog'
        );
    }

    /**
     * Send bot trade notification
     */
    public function sendBotTradeNotification($userId, $botName, $action, $amount, $asset = null)
    {
        // Get user's currency
        $user = User::find($userId);
        $currency = $user ? $user->currency : '$';
        
        $title = "Bot Trade {$action}";
        $message = "Your {$botName} bot has {$action} a trade";

        if ($asset) {
            $message .= " on {$asset}";
        }

        if ($amount) {
            $message .= " for {$currency}" . number_format($amount, 2);
        }

        $data = [
            'bot_name' => $botName,
            'action' => $action,
            'amount' => $amount,
            'asset' => $asset,
            'timestamp' => Carbon::now()->toISOString(),
        ];

        return $this->create(
            $userId,
            Notification::TYPE_BOT_TRADE,
            $title,
            $message,
            $data,
            Notification::PRIORITY_NORMAL,
            route('trading-bots.index'),
            'fas fa-robot'
        );
    }

    /**
     * Send profit/loss notification
     */
    public function sendProfitNotification($userId, $botName, $profit, $totalAmount = null)
    {
        // Get user's currency
        $user = User::find($userId);
        $currency = $user ? $user->currency : '$';
        
        $isProfit = $profit > 0;
        $action = $isProfit ? 'Profit Earned' : 'Loss Incurred';
        $verb = $isProfit ? 'earned' : 'incurred';

        $title = $action;
        $message = "Your {$botName} bot has {$verb} {$currency}" . number_format(abs($profit), 2);

        if ($totalAmount) {
            $message .= ". Trading balance: {$currency}" . number_format($totalAmount, 2);
        }

        $data = [
            'bot_name' => $botName,
            'profit_amount' => $profit,
            'total_amount' => $totalAmount,
            'is_profit' => $isProfit,
            'timestamp' => Carbon::now()->toISOString(),
        ];

        $priority = $isProfit ? Notification::PRIORITY_HIGH : Notification::PRIORITY_URGENT;
        $icon = $isProfit ? 'fas fa-chart-line' : 'fas fa-chart-line-down';

        return $this->create(
            $userId,
            Notification::TYPE_PROFIT_RETURN,
            $title,
            $message,
            $data,
            $priority,
            route('trading-bots.index'),
            $icon
        );
    }

    /**
     * Send bot completion notification
     */
    public function sendBotCompletionNotification($userId, $botName, $finalAmount, $totalProfit, $duration = null)
    {
        // Get user's currency
        $user = User::find($userId);
        $currency = $user ? $user->currency : '$';
        
        $profitText = $totalProfit >= 0 ? 'profit' : 'loss';

        $title = 'Bot Trading Completed';
        $message = "Your {$botName} bot has completed trading with a total {$profitText} of {$currency}" . number_format(abs($totalProfit), 2);

        if ($duration) {
            $message .= " over {$duration}";
        }

        $data = [
            'bot_name' => $botName,
            'final_amount' => $finalAmount,
            'total_profit' => $totalProfit,
            'duration' => $duration,
            'completion_time' => Carbon::now()->toISOString(),
        ];

        return $this->create(
            $userId,
            Notification::TYPE_BOT_COMPLETED,
            $title,
            $message,
            $data,
            Notification::PRIORITY_HIGH,
            route('trading-bots.index'),
            'fas fa-check-circle'
        );
    }

    /**
     * Send bot started notification
     */
    public function sendBotStartedNotification($userId, $botName, $amount, $duration = null)
    {
        // Get user's currency
        $user = User::find($userId);
        $currency = $user ? $user->currency : '$';
        
        $title = 'Bot Trading Started';
        $message = "Your {$botName} bot has started trading with {$currency}" . number_format($amount, 2);

        if ($duration) {
            $message .= " for {$duration}";
        }

        $data = [
            'bot_name' => $botName,
            'amount' => $amount,
            'duration' => $duration,
            'start_time' => Carbon::now()->toISOString(),
        ];

        return $this->create(
            $userId,
            Notification::TYPE_BOT_STARTED,
            $title,
            $message,
            $data,
            Notification::PRIORITY_NORMAL,
            route('trading-bots.index'),
            'fas fa-play-circle'
        );
    }

    /**
     * Send deposit notification
     */
    public function sendDepositNotification($userId, $amount, $method, $status = 'pending')
    {
        // Get user's currency
        $user = User::find($userId);
        $currency = $user ? $user->currency : '$';
        
        $title = 'Deposit ' . ucfirst($status);
        $message = "Your deposit of {$currency}" . number_format($amount, 2) . " via {$method} is {$status}";

        $data = [
            'amount' => $amount,
            'method' => $method,
            'status' => $status,
            'timestamp' => Carbon::now()->toISOString(),
        ];

        $priority = $status === 'approved' ? Notification::PRIORITY_HIGH : Notification::PRIORITY_NORMAL;

        return $this->create(
            $userId,
            Notification::TYPE_DEPOSIT,
            $title,
            $message,
            $data,
            $priority,
            route('user.deposits'),
            'fas fa-arrow-down'
        );
    }

    /**
     * Send withdrawal notification
     */
    public function sendWithdrawalNotification($userId, $amount, $method, $status = 'pending')
    {
        // Get user's currency
        $user = User::find($userId);
        $currency = $user ? $user->currency : '$';
        
        $title = 'Withdrawal ' . ucfirst($status);
        $message = "Your withdrawal of {$currency}" . number_format($amount, 2) . " via {$method} is {$status}";

        $data = [
            'amount' => $amount,
            'method' => $method,
            'status' => $status,
            'timestamp' => Carbon::now()->toISOString(),
        ];

        $priority = $status === 'approved' ? Notification::PRIORITY_HIGH : Notification::PRIORITY_NORMAL;

        return $this->create(
            $userId,
            Notification::TYPE_WITHDRAWAL,
            $title,
            $message,
            $data,
            $priority,
            route('user.withdrawals'),
            'fas fa-arrow-up'
        );
    }

    /**
     * Send system notification to all users or specific users
     */
    public function sendSystemNotification($title, $message, $userIds = null, $priority = 'normal')
    {
        $data = [
            'system_wide' => is_null($userIds),
            'timestamp' => Carbon::now()->toISOString(),
        ];

        if (is_null($userIds)) {
            // Send to all users
            $users = User::all();
            foreach ($users as $user) {
                $this->create(
                    $user->id,
                    Notification::TYPE_SYSTEM,
                    $title,
                    $message,
                    $data,
                    $priority,
                    null,
                    'fas fa-bullhorn'
                );
            }
        } else {
            // Send to specific users
            foreach ((array) $userIds as $userId) {
                $this->create(
                    $userId,
                    Notification::TYPE_SYSTEM,
                    $title,
                    $message,
                    $data,
                    $priority,
                    null,
                    'fas fa-bullhorn'
                );
            }
        }
    }

    /**
     * Get user notifications with pagination
     */
    public function getUserNotifications($userId, $limit = 10, $unreadOnly = false)
    {
        $query = Notification::where('user_id', $userId)
            ->orderBy('created_at', 'desc');

        if ($unreadOnly) {
            $query->unread();
        }

        return $query->paginate($limit);
    }

    /**
     * Get unread notification count for user
     */
    public function getUnreadCount($userId)
    {
        return Notification::where('user_id', $userId)->unread()->count();
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($notificationId, $userId = null)
    {
        $query = DB::table('notifications')->where('id', $notificationId);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $notification = $query->first();

        if ($notification && !$notification->read_at) {
            return DB::table('notifications')
                ->where('id', $notificationId)
                ->update(['read_at' => Carbon::now()]);
        }

        return false;
    }

    /**
     * Mark all notifications as read for a user
     */
    public function markAllAsRead($userId)
    {
        return DB::table('notifications')
            ->where('user_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => Carbon::now()]);
    }

    /**
     * Delete notification
     */
    public function deleteNotification($notificationId, $userId = null)
    {
        $query = Notification::where('id', $notificationId);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        return $query->delete();
    }

    /**
     * Send KYC application submitted notification
     */
    public function sendKycApplicationNotification($userId, $applicationId, $documentType)
    {
        $title = 'KYC Application Submitted';
        $message = "Your identity verification application ({$documentType}) has been submitted and is under review.";

        $data = [
            'application_id' => $applicationId,
            'document_type' => $documentType,
            'status' => 'under_review',
            'submitted_time' => Carbon::now()->toISOString(),
        ];

        return $this->create(
            $userId,
            Notification::TYPE_KYC,
            $title,
            $message,
            $data,
            Notification::PRIORITY_NORMAL,
            null,
            'fas fa-id-card'
        );
    }

    /**
     * Send KYC verification approved notification
     */
    public function sendKycApprovedNotification($userId, $applicationId, $documentType)
    {
        $title = 'Identity Verification Approved';
        $message = "Congratulations! Your identity verification application has been approved. Your account is now fully verified.";

        $data = [
            'application_id' => $applicationId,
            'document_type' => $documentType,
            'status' => 'verified',
            'approved_time' => Carbon::now()->toISOString(),
        ];

        return $this->create(
            $userId,
            Notification::TYPE_KYC,
            $title,
            $message,
            $data,
            Notification::PRIORITY_HIGH,
            null,
            'fas fa-check-circle'
        );
    }

    /**
     * Send KYC verification rejected notification
     */
    public function sendKycRejectedNotification($userId, $applicationId, $documentType, $reason = null)
    {
        $title = 'Identity Verification Rejected';
        $message = "Your identity verification application has been rejected";

        if ($reason) {
            $message .= ". Reason: {$reason}";
        } else {
            $message .= ". Please contact support for more information or resubmit with correct documents.";
        }

        $data = [
            'application_id' => $applicationId,
            'document_type' => $documentType,
            'status' => 'rejected',
            'reason' => $reason,
            'rejected_time' => Carbon::now()->toISOString(),
        ];

        return $this->create(
            $userId,
            Notification::TYPE_KYC,
            $title,
            $message,
            $data,
            Notification::PRIORITY_HIGH,
            route('verification'),
            'fas fa-times-circle'
        );
    }

    /**
     * Send withdrawal request notification
     */
    public function sendWithdrawalRequestNotification($userId, $amount, $paymentMethod, $status = 'pending')
    {
        // Get user's currency
        $user = User::find($userId);
        $currency = $user ? $user->currency : '$';
        
        $title = 'Withdrawal Request Submitted';
        $message = "Your withdrawal request of {$currency}" . number_format($amount, 2) . " via {$paymentMethod} has been submitted";

        if ($status === 'pending') {
            $message .= " and is awaiting processing.";
        }

        $data = [
            'amount' => $amount,
            'payment_method' => $paymentMethod,
            'status' => $status,
            'request_time' => Carbon::now()->toISOString(),
        ];

        return $this->create(
            $userId,
            Notification::TYPE_WITHDRAWAL,
            $title,
            $message,
            $data,
            Notification::PRIORITY_NORMAL,
            route('withdrawals'),
            'fas fa-arrow-up'
        );
    }

    /**
     * Send withdrawal processed notification
     */
    public function sendWithdrawalProcessedNotification($userId, $amount, $paymentMethod, $status = 'processed')
    {
        // Get user's currency
        $user = User::find($userId);
        $currency = $user ? $user->currency : '$';
        
        $statusText = $status === 'processed' ? 'processed successfully' : 'approved';
        $title = 'Withdrawal ' . ucfirst($statusText);
        $message = "Your withdrawal of {$currency}" . number_format($amount, 2) . " via {$paymentMethod} has been {$statusText}.";

        $data = [
            'amount' => $amount,
            'payment_method' => $paymentMethod,
            'status' => $status,
            'processed_time' => Carbon::now()->toISOString(),
        ];

        return $this->create(
            $userId,
            Notification::TYPE_WITHDRAWAL,
            $title,
            $message,
            $data,
            Notification::PRIORITY_HIGH,
            route('withdrawals'),
            'fas fa-check-circle'
        );
    }

    /**
     * Send withdrawal rejected notification
     */
    public function sendWithdrawalRejectedNotification($userId, $amount, $paymentMethod, $reason = null)
    {
        // Get user's currency
        $user = User::find($userId);
        $currency = $user ? $user->currency : '$';
        
        $title = 'Withdrawal Rejected';
        $message = "Your withdrawal request of {$currency}" . number_format($amount, 2) . " via {$paymentMethod} has been rejected";

        if ($reason) {
            $message .= ". Reason: {$reason}";
        } else {
            $message .= ". Please contact support for more information.";
        }

        $data = [
            'amount' => $amount,
            'payment_method' => $paymentMethod,
            'status' => 'rejected',
            'reason' => $reason,
            'rejected_time' => Carbon::now()->toISOString(),
        ];

        return $this->create(
            $userId,
            Notification::TYPE_WITHDRAWAL,
            $title,
            $message,
            $data,
            Notification::PRIORITY_HIGH,
            route('withdrawals'),
            'fas fa-times-circle'
        );
    }

    /**
     * Delete old notifications (older than specified days)
     */
    public function deleteOldNotifications($days = 30)
    {
        return Notification::where('created_at', '<', Carbon::now()->subDays($days))->delete();
    }

    /**
     * Send copy trade executed notification
     */
    public function sendCopyTradeExecutedNotification($userId, $expertName, $asset, $tradeDirection, $copyAmount, $entryPrice)
    {
        $directionText = strtoupper($tradeDirection);
        
        $title = "Trade Copied from {$expertName}";
        $message = "Successfully copied {$expertName}'s {$directionText} trade on {$asset} for \$" . number_format($copyAmount, 2) . " at \$" . number_format($entryPrice, 2);

        $data = [
            'expert_name' => $expertName,
            'asset' => $asset,
            'trade_direction' => $tradeDirection,
            'copy_amount' => $copyAmount,
            'entry_price' => $entryPrice,
            'timestamp' => Carbon::now()->toISOString(),
        ];

        return $this->create(
            $userId,
            'copy_trade',
            $title,
            $message,
            $data,
            Notification::PRIORITY_NORMAL,
            route('copy-trading.index'),
            'fas fa-copy'
        );
    }

    /**
     * Send copy trade closed notification (profit or loss)
     */
    public function sendCopyTradeClosedNotification($userId, $expertName, $asset, $tradeDirection, $profitLoss, $isProfit, $exitPrice, $profitPercentage)
    {
        // Get user's currency
        $user = User::find($userId);
        $currency = $user ? $user->currency : '$';
        
        $resultText = $isProfit ? 'Profit' : 'Loss';
        $emoji = $isProfit ? '📈' : '📉';
        $directionText = strtoupper($tradeDirection);
        
        $title = "{$emoji} Copy Trade Closed - {$resultText}";
        $message = "{$expertName}'s {$directionText} trade on {$asset} closed";
        
        if ($isProfit) {
            $message .= " with a profit of {$currency}" . number_format(abs($profitLoss), 2) . " (+" . number_format($profitPercentage, 2) . "%)";
        } else {
            $message .= " with a loss of {$currency}" . number_format(abs($profitLoss), 2) . " (-" . number_format($profitPercentage, 2) . "%)";
        }

        $data = [
            'expert_name' => $expertName,
            'asset' => $asset,
            'trade_direction' => $tradeDirection,
            'profit_loss' => $profitLoss,
            'is_profit' => $isProfit,
            'exit_price' => $exitPrice,
            'profit_percentage' => $profitPercentage,
            'timestamp' => Carbon::now()->toISOString(),
        ];

        $priority = $isProfit ? Notification::PRIORITY_HIGH : Notification::PRIORITY_URGENT;
        $icon = $isProfit ? 'fas fa-arrow-up text-success' : 'fas fa-arrow-down text-danger';

        return $this->create(
            $userId,
            'copy_trade_closed',
            $title,
            $message,
            $data,
            $priority,
            route('copy-trading.index'),
            $icon
        );
    }

    /**
     * Send expert new trade alert
     */
    public function sendExpertTradeAlertNotification($userId, $expertName, $asset, $tradeDirection, $expertAmount)
    {
        // Get user's currency
        $user = User::find($userId);
        $currency = $user ? $user->currency : '$';
        
        $directionText = strtoupper($tradeDirection);
        
        $title = "{$expertName} Opened New Trade";
        $message = "{$expertName} just opened a {$directionText} position on {$asset} with {$currency}" . number_format($expertAmount, 2) . ". Your copy trade has been automatically executed.";

        $data = [
            'expert_name' => $expertName,
            'asset' => $asset,
            'trade_direction' => $tradeDirection,
            'expert_amount' => $expertAmount,
            'timestamp' => Carbon::now()->toISOString(),
        ];

        return $this->create(
            $userId,
            'expert_trade_alert',
            $title,
            $message,
            $data,
            Notification::PRIORITY_NORMAL,
            route('copy-trading.index'),
            'fas fa-bell'
        );
    }

    /**
     * Send copy subscription started notification
     */
    public function sendCopySubscriptionStartedNotification($userId, $expertName, $copySettings)
    {
        $title = "Now Copying {$expertName}";
        $message = "You are now automatically copying all trades from {$expertName}. Copy settings have been applied.";

        $data = [
            'expert_name' => $expertName,
            'copy_settings' => $copySettings,
            'started_time' => Carbon::now()->toISOString(),
        ];

        return $this->create(
            $userId,
            'copy_subscription',
            $title,
            $message,
            $data,
            Notification::PRIORITY_NORMAL,
            route('copy-trading.index'),
            'fas fa-user-check'
        );
    }

    /**
     * Get default icon for notification type
     */
    private function getDefaultIcon($type)
    {
        $iconMap = [
            Notification::TYPE_BOT_TRADE => 'fas fa-robot',
            Notification::TYPE_PROFIT_RETURN => 'fas fa-chart-line',
            Notification::TYPE_BOT_COMPLETED => 'fas fa-check-circle',
            Notification::TYPE_BOT_STARTED => 'fas fa-play-circle',
            Notification::TYPE_DEPOSIT => 'fas fa-arrow-down',
            Notification::TYPE_WITHDRAWAL => 'fas fa-arrow-up',
            Notification::TYPE_KYC => 'fas fa-id-card',
            Notification::TYPE_ACCOUNT => 'fas fa-user',
            Notification::TYPE_SYSTEM => 'fas fa-cog',
            Notification::TYPE_GENERAL => 'fas fa-bell',
            'copy_trade' => 'fas fa-copy',
            'copy_trade_closed' => 'fas fa-chart-line',
            'expert_trade_alert' => 'fas fa-bell',
            'copy_subscription' => 'fas fa-user-check',
            'copy_subscription_completed' => 'fas fa-trophy',
        ];

        return $iconMap[$type] ?? 'fas fa-bell';
    }

    /**
     * Get notification statistics for user
     */
    public function getNotificationStats($userId)
    {
        $total = Notification::where('user_id', $userId)->count();
        $unread = Notification::where('user_id', $userId)->unread()->count();
        $byType = Notification::where('user_id', $userId)
            ->selectRaw('type, count(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();

        return [
            'total' => $total,
            'unread' => $unread,
            'read' => $total - $unread,
            'by_type' => $byType,
        ];
    }
}
