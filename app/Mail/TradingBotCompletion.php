<?php

namespace App\Mail;

use App\Models\User;
use App\Models\UserTradingBot;
use App\Models\Settings;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class TradingBotCompletion extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public $user, $userBot, $netProfit, $returnAmount, $settings, $subject;

    public function __construct(User $user, UserTradingBot $userBot, $netProfit, $returnAmount, $subject)
    {
        $this->user = $user;
        $this->userBot = $userBot;
        $this->netProfit = $netProfit;
        $this->returnAmount = $returnAmount;
        $this->subject = $subject;
        $this->settings = Settings::where('id', 1)->first();
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.trading-bot-completion')
                    ->subject($this->subject)
                    ->with([
                        'user' => $this->user,
                        'plan' => $this->userBot->tradingBot->name,
                        'amount' => $this->netProfit,
                        'plandate' => $this->userBot->expires_at->format('M d, Y H:i'),
                        'settings' => $this->settings
                    ]);
    }
}
