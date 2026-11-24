<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ManualTrade;
use App\Models\Tp_Transaction;
use App\Models\TradingAsset;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ManualTradingController extends Controller
{
    private $instruments = [
        'crypto' => ['BTC/USD', 'ETH/USD', 'BTC/USDT', 'ETH/USDT', 'SOL/USD', 'XRP/USD'],
        'forex' => ['EUR/USD', 'GBP/USD', 'USD/JPY', 'USD/CHF', 'AUD/USD', 'USD/CAD'],
        'indices' => ['NASDAQ 100', 'S&P 500', 'Dow Jones', 'FTSE 100'],
    ];
    private $leverages = [
        'crypto' => [1, 2, 5, 10, 20],
        'forex' => [1, 10, 20, 50, 100],
        'indices' => [1, 2, 5, 10, 20],
    ];

    public function index()
    {
        return view('user.manual-trading', [
            'title' => 'Manual Trading',
            'instruments' => $this->instruments,
            'leverages' => $this->leverages,
            'trades' => ManualTrade::where('user_id', Auth::id())->latest()->paginate(15),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category' => 'required|in:crypto,forex,indices',
            'instrument' => 'required|string|max:40',
            'direction' => 'required|in:BUY,SELL',
            'amount' => 'required|numeric|min:1|max:100000000',
            'leverage' => 'required|integer|in:1,2,5,10,20,50,100',
            'duration_minutes' => 'required|integer|in:1,5,15,30,60,240,1440',
            'submission_key' => 'required|string|max:64',
        ]);

        if (!in_array($data['instrument'], $this->instruments[$data['category']], true)) {
            return back()->withErrors(['instrument' => 'That instrument is not available in this category.'])->withInput();
        }
        if (!in_array((int) $data['leverage'], $this->leverages[$data['category']], true)) {
            return back()->withErrors(['leverage' => 'That leverage is not available for the selected category.'])->withInput();
        }

        $userId = Auth::id();
        $cacheKey = 'manual-trade-submit:' . $userId . ':' . sha1($data['submission_key']);
        if (!Cache::add($cacheKey, 1, now()->addMinutes(10))) {
            return back()->with('message', 'This trade request was already submitted.')->withInput();
        }
        try {
        $trade = DB::transaction(function () use ($data, $userId) {
            $user = User::whereKey($userId)->lockForUpdate()->firstOrFail();
            if (!$user->trading_enabled) {
                throw new \DomainException('Manual trading is disabled for your account.');
            }
            if ((float) $user->account_bal < (float) $data['amount']) {
                throw new \DomainException('Insufficient account balance to open this trade.');
            }

            $symbol = strtoupper(str_replace('/', '', $data['instrument']));
            $asset = TradingAsset::where('is_active', true)
                ->where(function ($query) use ($data, $symbol) {
                    $query->whereRaw("UPPER(REPLACE(symbol, '/', '')) = ?", [$symbol])
                        ->orWhereRaw('UPPER(name) = ?', [strtoupper($data['instrument'])]);
                })->first();
            if (!$asset && $data['category'] === 'crypto' && strpos($data['instrument'], '/') !== false) {
                $baseSymbol = strtoupper(explode('/', $data['instrument'])[0] . 'USD');
                $asset = TradingAsset::where('is_active', true)
                    ->whereRaw("UPPER(REPLACE(symbol, '/', '')) = ?", [$baseSymbol])
                    ->first();
            }

            $trade = ManualTrade::create([
                'user_id' => $user->id,
                'category' => $data['category'],
                'instrument' => $data['instrument'],
                'direction' => $data['direction'],
                'amount' => $data['amount'],
                'leverage' => $data['leverage'],
                'duration_minutes' => $data['duration_minutes'],
                'entry_price' => $asset ? $asset->current_price : null,
                'status' => 'active',
            ]);

            $user->account_bal = round((float) $user->account_bal - (float) $data['amount'], 2);
            $user->save();

            Tp_Transaction::create([
                'user' => $user->id,
                'plan' => 'Manual trade opened: ' . $trade->instrument . ' ' . $trade->direction,
                'amount' => -$data['amount'],
                'type' => 'ROI',
            ]);

            return $trade;
        });
        } catch (\DomainException $e) {
            Cache::forget($cacheKey);
            return back()->withErrors(['amount' => $e->getMessage()])->withInput();
        } catch (\Throwable $e) {
            Cache::forget($cacheKey);
            throw $e;
        }

        try {
            Mail::raw(
                "Your manual trade is open.\nInstrument: {$trade->instrument}\nCategory: {$trade->category}\nDirection: {$trade->direction}\nAmount: {$trade->amount}\nLeverage: {$trade->leverage}x\nDuration: {$trade->duration_minutes} minutes\nEntry price: " . ($trade->entry_price ?: 'Not available') . "\nStatus: Active",
                function ($message) use ($trade) {
                    $message->to($trade->user->email)->subject('Manual trade opened');
                }
            );
        } catch (\Exception $e) {
            report($e);
        }

        return redirect()->route('manual-trading.index')->with('success', 'Trade opened successfully.');
    }
}
