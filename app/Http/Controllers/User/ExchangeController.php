<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CryptoAccount;
use Illuminate\Support\Facades\Auth;
use App\Models\SettingsCont;
use App\Models\Settings;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\CryptoRecord;
use App\Traits\Apitrait;

class ExchangeController extends Controller
{
    use Apitrait;

    public function assetview()
    {
        $settings = SettingsCont::where('id', '1')->first();

        return view('user.asset', [
            'title' =>  'Exchange currency',
            'cbalance' => CryptoAccount::where('user_id', Auth::user()->id)->first(),
        ]);
    }

    public function history()
    {
        return view('user.crypto-transaction', [
            'title' => 'Swapping History',
            'transactions' => DB::table('crypto_records')->orderByDesc('id')->paginate(10),
        ]);
    }

    public function getprice($base, $quote, $amount)
    {
        $amount = filter_var($amount, FILTER_VALIDATE_FLOAT);
        if ($amount === false || $amount <= 0) {
            return response()->json(['status' => 400, 'message' => 'Amount must be greater than zero.'], 400);
        }

        $supported = ['usd', 'btc', 'eth', 'ltc', 'link', 'bnb', 'ada', 'aave', 'usdt', 'bch', 'xrp', 'xlm'];
        if (!in_array(strtolower($base), $supported, true) || !in_array(strtolower($quote), $supported, true) || strtolower($base) === strtolower($quote)) {
            return response()->json(['status' => 422, 'message' => 'Select two different supported currencies.'], 422);
        }

        $settings = SettingsCont::where('id', '1')->first();
        $fee_percentage = max(0, min(100, (float) ($settings->fee ?? 0)));
        $amount_after_fee = $amount - ($amount * $fee_percentage / 100);

        try {
            $baseUsd = strtolower($base) === 'usd' ? 1 : $this->get_rate($base, 'usd');
            $quoteUsd = strtolower($quote) === 'usd' ? 1 : $this->get_rate($quote, 'usd');
            if (!$baseUsd || !$quoteUsd) {
                throw new \Exception('A live rate is temporarily unavailable. Please try again shortly.');
            }
            $rate = $baseUsd / $quoteUsd;
            $quantity = $amount_after_fee * $rate;
            return response()->json([
                'status' => 200,
                'data' => round($quantity, 8),
                'rate' => round($rate, 12),
                'fee_percentage' => $fee_percentage,
                'updated_at' => now()->toIso8601String(),
            ]);

        } catch (\Exception $e) {
            return response()->json(['status' => 503, 'message' => $e->getMessage()], 503);
        }
    }


    public function exchange(Request $request)
    {
        $request->validate([
            'source' => 'required|string|in:usd,btc,eth,ltc,link,bnb,ada,aave,usdt,bch,xrp,xlm',
            'destination' => 'required|string|in:usd,btc,eth,ltc,link,bnb,ada,aave,usdt,bch,xrp,xlm|different:source',
            'amount' => 'required|numeric|gt:0|max:1000000000',
            'request_key' => 'required|string|max:64',
        ]);
        $source = strtolower($request->source);
        $destination = strtolower($request->destination);
        $amount = (float) $request->amount;
        $quote = $this->getprice($source, $destination, $amount)->getData(true);
        if ($quote['status'] !== 200) {
            return response()->json(['status' => 503, 'message' => $quote['message'] ?? 'Live price unavailable.'], 503);
        }
        $quantity = (float) $quote['data'];
        $userId = Auth::id();
        $cacheKey = 'crypto-swap:' . $userId . ':' . sha1($request->request_key);
        if (!Cache::add($cacheKey, 1, now()->addMinutes(10))) {
            return response()->json(['status' => 409, 'message' => 'This swap request was already processed.'], 409);
        }

        try {
            DB::transaction(function () use ($source, $destination, $amount, $quantity, $userId) {
                $user = User::whereKey($userId)->lockForUpdate()->firstOrFail();
                $wallet = CryptoAccount::where('user_id', $user->id)->lockForUpdate()->first();
                if (!$wallet) {
                    $wallet = new CryptoAccount();
                    $wallet->user_id = $user->id;
                    $wallet->save();
                    $wallet = CryptoAccount::whereKey($wallet->id)->lockForUpdate()->firstOrFail();
                }

                if ($source === 'usd') {
                    if ((float) $user->account_bal < $amount) {
                        throw new \DomainException('Insufficient balance in your source account.');
                    }
                    $user->account_bal = round((float) $user->account_bal - $amount, 2);
                    $user->save();
                } else {
                    if (!isset($wallet->$source) || (float) $wallet->$source < $amount) {
                        throw new \DomainException('Insufficient balance in your source account.');
                    }
                    $wallet->$source = round((float) $wallet->$source - $amount, 8);
                }

                if ($destination === 'usd') {
                    $user->account_bal = round((float) $user->account_bal + $quantity, 2);
                    $user->save();
                } else {
                    if (!isset($wallet->$destination)) {
                        throw new \DomainException('Unsupported destination wallet.');
                    }
                    $wallet->$destination = round((float) $wallet->$destination + $quantity, 8);
                }
                $wallet->save();

                $record = new CryptoRecord();
                $record->source = strtoupper($source);
                $record->dest = strtoupper($destination);
                $record->amount = $amount;
                $record->quantity = $quantity;
                $record->save();
            });
        } catch (\DomainException $e) {
            Cache::forget($cacheKey);
            return response()->json(['status' => 422, 'message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            Cache::forget($cacheKey);
            throw $e;
        }

        return response()->json(['status' => 200, 'success' => 'Exchange successful.']);
    }

    public function getBalance($coin)
    {
        $settings = Settings::where('id', '1')->first();
        $settingss = SettingsCont::where('id', '1')->first();
        $user = Auth::user();
        $acntbals = DB::table('crypto_accounts')->where('user_id', $user->id)->first();

        if (empty($acntbals->$coin)) {
            $balanc = 0;
        } else {
            $balanc = $acntbals->$coin;
        }

        $dollar = $this->get_rate($coin, 'usd');
        $mainbal = $balanc * $dollar;

        if ($settings->s_currency == 'USD') {
            $price = number_format(round($mainbal));
        } else {
            if (empty($settingss->currency_rate)) {
                $rate = 1;
            } else {
                $rate = $settingss->currency_rate;
            }

            $othercurr = $mainbal * $rate;
            $price = number_format(round($othercurr));
        }

        return response()->json([
            'data' => $price,
            'status' => 200
        ]);
    }
}
