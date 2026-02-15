<?php

namespace App\Mail;

use App\Models\CopyTrade;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CopyTradeNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $copyTrade;

    /**
     * Create a new message instance.
     */
    public function __construct(CopyTrade $copyTrade)
    {
        $this->copyTrade = $copyTrade;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $subject = $this->copyTrade->profit_loss >= 0
            ? 'Copy Trade Profit - ' . $this->copyTrade->expertTrader->name
            : 'Copy Trade Update - ' . $this->copyTrade->expertTrader->name;

        return $this->subject($subject)
                    ->view('emails.copy-trade-notification')
                    ->with([
                        'copyTrade' => $this->copyTrade,
                        'isProfit' => $this->copyTrade->profit_loss >= 0,
                        'formattedAmount' => '$' . number_format(abs($this->copyTrade->profit_loss), 2),
                        'expertName' => $this->copyTrade->expertTrader->name,
                        'assetSymbol' => $this->copyTrade->asset_symbol,
                        'tradeType' => ucfirst($this->copyTrade->trade_type),
                        'copyAmount' => '$' . number_format($this->copyTrade->copy_amount, 2),
                        'percentage' => number_format($this->copyTrade->profit_loss_percentage, 2) . '%'
                    ]);
    }
}
