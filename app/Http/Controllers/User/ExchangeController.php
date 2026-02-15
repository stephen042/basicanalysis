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
        if ($amount <= 0) {
            return response()->json(['status' => 400, 'message' => 'Amount must be greater than zero.'], 400);
        }

        $settings = SettingsCont::where('id', '1')->first();
        $fee_percentage = $settings->fee ?? 0;
        $amount_after_fee = $amount - ($amount * $fee_percentage / 100);

        $prices = 0;

        try {
            // Handle direct USD to USDT and vice-versa, assuming 1:1 rate
            if (($base == 'usd' && $quote == 'usdt') || ($base == 'usdt' && $quote == 'usd')) {
                $prices = $amount_after_fee;
            }
            // Handle conversion from USD to a cryptocurrency
            elseif ($base == 'usd') {
                $quote_rate = $this->get_rate($quote, 'usd');
                if (is_null($quote_rate) || $quote_rate == 0) {
                    throw new \Exception("Could not fetch the price for " . strtoupper($quote));
                }
                $prices = $amount_after_fee / $quote_rate;
            }
            // Handle conversion from a cryptocurrency to USD
            elseif ($quote == 'usd') {
                $base_rate = $this->get_rate($base, 'usd');
                if (is_null($base_rate)) {
                    throw new \Exception("Could not fetch the price for " . strtoupper($base));
                }
                $prices = $amount_after_fee * $base_rate;
            }
            // Handle conversion between two different cryptocurrencies
            else {
                $base_rate_in_usd = $this->get_rate($base, 'usd');
                $quote_rate_in_usd = $this->get_rate($quote, 'usd');

                if (is_null($base_rate_in_usd) || is_null($quote_rate_in_usd) || $quote_rate_in_usd == 0) {
                    throw new \Exception("Could not fetch conversion rates.");
                }

                // Convert the amount of 'base' coin to its USD value, then convert that USD value to the 'quote' coin
                $base_in_usd = $amount_after_fee * $base_rate_in_usd;
                $prices = $base_in_usd / $quote_rate_in_usd;
            }

            return response()->json(['status' => 200, 'data' => round($prices, 8)]);

        } catch (\Exception $e) {
            return response()->json(['status' => 500, 'message' => $e->getMessage()], 500);
        }
    }


    public function exchange(Request $request)
    {

        $cryptobalances = CryptoAccount::where('user_id', Auth::user()->id)->first();
        $acntbal = Auth::user()->account_bal;
        $src = $request->source;
        $cdest = $request->destination;
        $user = User::find(Auth::user()->id);

        if ($request->source == 'usd') {
            if ($acntbal < $request->amount) {
                return response()->json(['status' => 201, 'message' => 'Insuficient fund in your source account']);
            }

            User::where('id', Auth::user()->id)->update([
                'account_bal' => $acntbal - $request->amount,
            ]);

            if ($request->destination == 'btc') {
                DB::table('crypto_accounts')
                    ->where('user_id', $user->id)
                    ->update([
                        'btc' => $cryptobalances->btc + $request->quantity,
                    ]);
            }
            if ($request->destination == 'eth') {
                DB::table('crypto_accounts')
                    ->where('user_id', $user->id)
                    ->update([
                        'eth' => $cryptobalances->eth + $request->quantity,
                    ]);
            }
            if ($request->destination == 'link') {
                DB::table('crypto_accounts')
                    ->where('user_id', $user->id)
                    ->update([
                        'link' => $cryptobalances->link + $request->quantity,
                    ]);
            }
            if ($request->destination == 'usdt') {
                DB::table('crypto_accounts')
                    ->where('user_id', $user->id)
                    ->update([
                        'usdt' => $cryptobalances->usdt + $request->quantity,
                    ]);
            }
            if ($request->destination == 'ltc') {
                DB::table('crypto_accounts')
                    ->where('user_id', $user->id)
                    ->update([
                        'ltc' => $cryptobalances->ltc + $request->quantity,
                    ]);
            }
            if ($request->destination == 'bch') {
                DB::table('crypto_accounts')
                    ->where('user_id', $user->id)
                    ->update([
                        'bch' => $cryptobalances->bch + $request->quantity,
                    ]);
            }
            if ($request->destination == 'xrp') {
                DB::table('crypto_accounts')
                    ->where('user_id', $user->id)
                    ->update([
                        'xrp' => $cryptobalances->xrp + $request->quantity,
                    ]);
            }
            if ($request->destination == 'bnb') {
                DB::table('crypto_accounts')
                    ->where('user_id', $user->id)
                    ->update([
                        'bnb' => $cryptobalances->bnb + $request->quantity,
                    ]);
            }
            if ($request->destination == 'ada') {
                DB::table('crypto_accounts')
                    ->where('user_id', $user->id)
                    ->update([
                        'ada' => $cryptobalances->ada + $request->quantity,
                    ]);
            }
            if ($request->destination == 'xlm') {
                DB::table('crypto_accounts')
                    ->where('user_id', $user->id)
                    ->update([
                        'xlm' => $cryptobalances->xlm + $request->quantity,
                    ]);
            }
            if ($request->destination == 'aave') {
                DB::table('crypto_accounts')
                    ->where('user_id', $user->id)
                    ->update([
                        'aave' => $cryptobalances->aave + $request->quantity,
                    ]);
            }

            $record = new CryptoRecord();
            $record->source = strtoupper($request->source);
            $record->dest = strtoupper($request->destination);
            $record->amount = $request->amount;
            $record->quantity = $request->quantity;
            $record->save();

            return response()->json(['status' => 200, 'success' => 'Exchange Successful, Refreshing your Balances']);
        }

        if ($request->source != 'usd' and  $request->destination != 'usd') {

            if ($cryptobalances->$src < $request->amount) {
                return response()->json(['status' => 201, 'message' => 'Insuficient fund in your source account']);
            }

            // $acnt = CryptoAccount::find($cryptobalances->id);
            // $acnt->$src = $cryptobalances->$src  - $request->amount;
            // $acnt->$cryptobalances->cdest = $cryptobalances->cdest + $request->quantity;
            // $acnt->save();


            CryptoAccount::where('user_id', $user->id)
                ->update([
                    $request->source => $cryptobalances->$src - $request->amount,
                ]);

            CryptoAccount::where('user_id', $user->id)
                ->update([
                    $request->destination => $cryptobalances->$cdest + $request->quantity,
                ]);

            $record = new CryptoRecord();
            $record->source = strtoupper($request->source);
            $record->dest = strtoupper($request->destination);
            $record->amount = $request->amount;
            $record->quantity = $request->quantity;
            $record->save();

            return response()->json(['status' => 200, 'success' => 'Exchange Successful, Refreshing your Balances']);
        }

        if ($request->source != 'usd' and  $request->destination == 'usd') {

            if ($cryptobalances->$src < $request->amount) {
                return response()->json(['status' => 201, 'message' => 'Insuficient fund in your source account']);
            }

            DB::table('crypto_accounts')
                ->where('user_id', $user->id)
                ->update([
                    $request->source => $cryptobalances->$src - $request->amount,
                ]);

            User::where('id', Auth::user()->id)->update([
                'account_bal' => $acntbal + $request->quantity,
            ]);

            $record = new CryptoRecord();
            $record->source = strtoupper($request->source);
            $record->dest = strtoupper($request->destination);
            $record->amount = $request->amount;
            $record->quantity = $request->quantity;
            $record->save();

            return response()->json(['status' => 200, 'success' => 'Exchange Successful, Refreshing your Balances']);
        }
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
