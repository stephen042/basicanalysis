<?php

namespace App\Services;

use App\Models\CompoundInvestment;
use App\Models\CompoundInvestmentContribution;
use App\Models\CompoundInvestmentReminder;
use App\Support\SchemaInspector;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CompoundInvestmentReminderService
{
    protected $notifications;

    public function __construct(NotificationService $notifications)
    {
        $this->notifications = $notifications;
    }

    public function sendDueReminders(): int
    {
        $sent = 0;
        $ids = CompoundInvestment::where('status', 'active')
            ->where('deposit_status', 'approved')
            ->where('recurring_amount', '>', 0)
            ->whereNotNull('next_recurring_due_at')
            ->where('next_recurring_due_at', '<=', now())
            ->orderBy('next_recurring_due_at')
            ->pluck('id');

        foreach ($ids as $id) {
            try {
                $reminder = DB::transaction(function () use ($id) {
                    $investment = CompoundInvestment::whereKey($id)->lockForUpdate()->first();
                    if (!$investment
                        || $investment->status !== 'active'
                        || $investment->deposit_status !== 'approved'
                        || (float) $investment->recurring_amount <= 0
                        || !$investment->next_recurring_due_at
                        || $investment->next_recurring_due_at->isFuture()) {
                        return null;
                    }

                    $scheduledFor = $investment->next_recurring_due_at->copy();
                    if (SchemaInspector::hasTable('compound_investment_contributions')) {
                        $pendingContributionQuery = CompoundInvestmentContribution::where('compound_investment_id', $investment->id)
                            ->where('scheduled_for', $scheduledFor)
                            ->where('status', 'pending');
                        if (SchemaInspector::hasColumn('compound_investment_contributions', 'balance_debited')) {
                            $pendingContributionQuery->where(function ($query) {
                                $query->whereNotNull('deposit_id')
                                    ->orWhere('balance_debited', true);
                            });
                        } else {
                            $pendingContributionQuery->whereNotNull('deposit_id');
                        }
                        if ($pendingContributionQuery->exists()) {
                            return null;
                        }
                    }

                    if (CompoundInvestmentReminder::where('compound_investment_id', $investment->id)
                        ->where('scheduled_for', $scheduledFor)
                        ->exists()) {
                        return null;
                    }

                    CompoundInvestmentReminder::create([
                        'compound_investment_id' => $investment->id,
                        'scheduled_for' => $scheduledFor,
                        'sent_at' => now(),
                    ]);

                    return [
                        'user_id' => $investment->user_id,
                        'investment_id' => $investment->id,
                        'amount' => (float) $investment->recurring_amount,
                        'frequency' => $investment->recurring_frequency,
                        'due_at' => $scheduledFor,
                    ];
                });
            } catch (QueryException $e) {
                // A concurrent scheduler process may have inserted the unique
                // reminder row first. That period is already handled.
                continue;
            } catch (\Throwable $e) {
                Log::error('Could not prepare compound investment reminder', [
                    'investment_id' => $id,
                    'error' => $e->getMessage(),
                ]);
                continue;
            }

            if (!$reminder) {
                continue;
            }

            $due = $reminder['due_at']->format('M j, Y');
            $amount = '$' . number_format($reminder['amount'], 2);
            $message = "Your compound investment recurring top-up of {$amount} is due. "
                . "Make your deposit to keep your investment contribution schedule up to date. "
                . "Next expected top-up date: {$due}.";

            $this->notifications->create(
                $reminder['user_id'],
                \App\Models\Notification::TYPE_GENERAL,
                'Compound recurring top-up due',
                $message,
                [
                    'investment_id' => $reminder['investment_id'],
                    'amount' => $reminder['amount'],
                    'frequency' => $reminder['frequency'],
                    'due_at' => $reminder['due_at']->toISOString(),
                ],
                \App\Models\Notification::PRIORITY_HIGH,
                route('compound-investment.show', $reminder['investment_id']),
                'fas fa-calendar-check'
            );

            try {
                $user = \App\Models\User::find($reminder['user_id']);
                if ($user && $user->email) {
                    Mail::raw($message, function ($mail) use ($user) {
                        $mail->to($user->email)->subject('Compound investment recurring top-up due');
                    });
                }
            } catch (\Throwable $e) {
                Log::error('Could not email compound investment reminder', [
                    'investment_id' => $reminder['investment_id'],
                    'user_id' => $reminder['user_id'],
                    'error' => $e->getMessage(),
                ]);
            }

            $sent++;
        }

        return $sent;
    }
}
