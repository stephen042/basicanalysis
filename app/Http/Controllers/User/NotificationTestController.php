<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationTestController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Test notification creation
     */
    public function test()
    {
        $userId = Auth::id();

        // Create sample notifications
        $this->notificationService->sendBotTradeNotification(
            $userId,
            'Bitcoin Scalper Pro',
            'opened',
            250.50,
            'BTC/USDT'
        );

        $this->notificationService->sendProfitNotification(
            $userId,
            'Ethereum Swing Trader',
            45.75,
            1250.75
        );

        $this->notificationService->sendBotCompletionNotification(
            $userId,
            'DeFi Yield Hunter',
            1580.25,
            180.25,
            '3 days'
        );

        $this->notificationService->sendDepositNotification(
            $userId,
            500.00,
            'Bitcoin',
            'approved'
        );

        $this->notificationService->sendWithdrawalNotification(
            $userId,
            200.00,
            'Bank Transfer',
            'pending'
        );

        return redirect()->route('user.notifications')->with('success', 'Test notifications created successfully!');
    }
}
