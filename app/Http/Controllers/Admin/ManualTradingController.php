<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ManualTrade;
use App\Models\Tp_Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ManualTradingController extends Controller
{
    public function index()
    {
        return view('admin.manual-trading.index', [
            'title' => 'Manual Trades',
            'trades' => ManualTrade::with('user')->latest()->paginate(30),
        ]);
    }

    public function settle(Request $request, ManualTrade $trade)
    {
        $data = $request->validate([
            'result_type' => 'required|in:profit,loss',
            'result_amount' => 'required|numeric|min:0|max:1000000000',
        ]);
        $amount = round((float) $data['result_amount'], 2);
        if ($data['result_type'] === 'loss' && $amount > (float) $trade->amount) {
            return back()->withErrors(['result_amount' => 'A loss cannot exceed the amount reserved for this trade.']);
        }

        try {
        $userId = DB::transaction(function () use ($trade, $data, $amount) {
            $lockedTrade = ManualTrade::whereKey($trade->id)->lockForUpdate()->firstOrFail();
            if ($lockedTrade->status !== 'active') {
                throw new \DomainException('This trade has already been completed.');
            }
            $user = User::whereKey($lockedTrade->user_id)->lockForUpdate()->firstOrFail();

            // The opening stake was reserved already. Return only the unused
            // principal; a profit is separately credited to the earnings wallet.
            $profit = $data['result_type'] === 'profit' ? $amount : 0;
            $loss = $data['result_type'] === 'loss' ? $amount : 0;
            $principalReturn = (float) $lockedTrade->amount;
            $user->account_bal = round((float) $user->account_bal + $principalReturn - $loss, 2);
            if ($profit > 0) {
                $user->roi = round((float) $user->roi + $profit, 2);
            }
            $user->save();

            $signedResult = $profit - $loss;
            $lockedTrade->result_amount = $signedResult;
            $lockedTrade->status = 'completed';
            $lockedTrade->completed_at = now();
            $lockedTrade->save();

            Tp_Transaction::create([
                'user' => $user->id,
                'plan' => 'Manual trade principal returned: ' . $lockedTrade->instrument,
                'amount' => $principalReturn,
                'type' => 'Transfer',
            ]);
            Tp_Transaction::create([
                'user' => $user->id,
                'plan' => 'Manual trade ' . ($profit > 0 ? 'profit' : 'loss') . ': ' . $lockedTrade->instrument,
                'amount' => $signedResult,
                'type' => 'ROI',
            ]);

            return $user->id;
        });
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        $trade->refresh();
        try {
            $user = User::findOrFail($userId);
            Mail::raw(
                "Your manual trade has been completed.\nInstrument: {$trade->instrument}\nDirection: {$trade->direction}\nAmount: {$trade->amount}\nResult: " . ($trade->result_amount >= 0 ? 'Profit ' : 'Loss ') . number_format(abs((float) $trade->result_amount), 2) . "\nStatus: Completed",
                function ($message) use ($user) {
                    $message->to($user->email)->subject('Manual trade completed');
                }
            );
        } catch (\Exception $e) {
            report($e);
        }

        return back()->with('success', 'Trade completed and user balances updated.');
    }
}
