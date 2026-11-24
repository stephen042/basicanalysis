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
use App\Services\MarketPriceService;
use App\Traits\Apitrait;

class ExchangeController extends Controller
{
    use Apitrait;

    /**
     * Cryptocurrencies available for swapping. This mirrors the columns of the
     * crypto_accounts table so that every asset a user can swap into or out of
     * actually has a wallet column to hold the balance.
     */
    private function supportedCoins(): array
    {
        return ['btc', 'eth', 'ltc', 'link', 'bnb', 'ada', 'aave', 'usdt', 'bch', 'xrp', 'xlm'];
    }

    public function assetview()
    {
        $cbalance = CryptoAccount::where('user_id', Auth::user()->id)->first();
        if (!$cbalance) {
            $cbalance = new CryptoAccount();
        }

        return view('user.asset', [
            'title' =>  'Exchange currency',
            'cbalance' => $cbalance,
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

        $base = strtolower((string) $base);
        $quote = strtolower((string) $quote);
        $supported = array_merge(['usd'], $this->supportedCoins());

        if (!in_array($base, $supported, true) || !in_array($quote, $supported, true) || $base === $quote) {
            return response()->json(['status' => 422, 'message' => 'Select two different supported currencies.'], 422);
        }

        $settings = SettingsCont::where('id', '1')->first();
        $fee_percentage = max(0, min(100, (float) ($settings->fee ?? 0)));

        $prices = app(MarketPriceService::class);
        $baseUsd = $base === 'usd' ? 1.0 : $prices->getUsdPrice($base);
        $quoteUsd = $quote === 'usd' ? 1.0 : $prices->getUsdPrice($quote);

        if ($baseUsd === null || $quoteUsd === null || (float) $quoteUsd <= 0) {
            return response()->json([
                'status' => 503,
                'message' => 'A live market rate is temporarily unavailable. Please try again in a moment.',
            ], 503);
        }

        $rate = (float) $baseUsd / (float) $quoteUsd;
        $amount_after_fee = $amount - ($amount * $fee_percentage / 100);
        $quantity = $amount_after_fee * $rate;

        return response()->json([
            'status' => 200,
            'data' => round($quantity, 8),
            'rate' => round($rate, 12),
            'fee_percentage' => $fee_percentage,
            'updated_at' => now()->toIso8601String(),
        ]);
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
        $rate = (float) ($quote['rate'] ?? 0);
        $feePercentage = (float) ($quote['fee_percentage'] ?? 0);
        $feeAmount = round($amount * $feePercentage / 100, 8);
        $userId = Auth::id();
        $cacheKey = 'crypto-swap:' . $userId . ':' . sha1($request->request_key);
        if (!Cache::add($cacheKey, 1, now()->addMinutes(10))) {
            return response()->json(['status' => 409, 'message' => 'This swap request was already processed.'], 409);
        }

        try {
            DB::transaction(function () use ($source, $destination, $amount, $quantity, $rate, $feePercentage, $feeAmount, $userId) {
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
                $record->exchange_rate = $rate;
                $record->fee_percentage = $feePercentage;
                $record->fee_amount = $feeAmount;
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

        $balanc = ($acntbals && isset($acntbals->$coin)) ? (float) $acntbals->$coin : 0;

        $dollar = $this->get_rate($coin, 'usd');
        $mainbal = $balanc * (float) ($dollar ?? 0);

        if ($settings->s_currency == 'USD') {
            $price = number_format(round($mainbal));
        } else {
            $rate = empty($settingss->currency_rate) ? 1 : $settingss->currency_rate;
            $othercurr = $mainbal * $rate;
            $price = number_format(round($othercurr));
        }

        return response()->json([
            'data' => $price,
            'status' => 200
        ]);
    }
}
