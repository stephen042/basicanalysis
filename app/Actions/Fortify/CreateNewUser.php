<?php

namespace App\Actions\Fortify;

use App\Mail\WelcomeEmail;
use App\Models\User;
use App\Models\Settings;
use App\Models\Agent;
use App\Models\CryptoAccount;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Validate and create a newly registered user.
     *
     * @param  array  $input
     * @return \App\Models\User
     */
    public function create(array $input)
    {
        try {
            $settings = Schema::hasTable('settings')
                ? (Settings::where('id', 1)->first() ?: new Settings())
                : new Settings();
        } catch (\Throwable $e) {
            $settings = new Settings();
        }
        $request = request();
        $userColumns = User::databaseColumns((new User())->getTable());
        $cryptoColumns = User::databaseColumns((new CryptoAccount())->getTable());
        $supportsUsername = in_array('username', $userColumns, true);

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => $this->passwordRules(),
        ];
        if ($supportsUsername) {
            $rules['username'] = ['required', 'string', 'max:255', 'unique:users,username'];
        }

        if ($settings->captcha == "true") {
            $captchaRules = $rules + [
                'g-recaptcha-response' => 'required|captcha',
            ];
            $captchaRules['terms'] = Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['required', 'accepted'] : '';
            Validator::make($input, $captchaRules)->validate();
        } else {
            $captchaRules = $rules;
            $captchaRules['captcha'] = ['required', 'numeric', function ($attribute, $value, $fail) use ($input) {
                    if ($value != ($input['captcha_confirmation'] ?? null)) {
                        $fail('The math answer is incorrect. Please try again.');
                    }
                }];
            $captchaRules['terms'] = Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['required', 'accepted'] : '';
            Validator::make($input, $captchaRules)->validate();
        }

        $referrerId = null;
        if (session('ref_by') && $supportsUsername) {
            $ref_by = session('ref_by');
            $user = User::where('username', $ref_by)->first();
            $referrerId = $user ? $user->id : null;
        } elseif (session('ref_by') && in_array('id', $userColumns, true) && ctype_digit((string) session('ref_by'))) {
            $referrerId = User::whereKey(session('ref_by'))->value('id');
        } elseif (!empty($input['ref_by'])) {
            if ($supportsUsername) {
                $sponsor = User::where('username', $input['ref_by'])->first();
                $referrerId = $sponsor ? $sponsor->id : null;
            }
        }

        $attributes = [
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
        ];
        foreach (['phone', 'country', 'currency'] as $column) {
            if (in_array($column, $userColumns, true) && array_key_exists($column, $input)) {
                $attributes[$column] = $input[$column];
            }
        }
        if ($supportsUsername) {
            $attributes['username'] = $input['username'];
        }
        if (in_array('ref_by', $userColumns, true)) {
            $attributes['ref_by'] = $referrerId;
        }
        if (in_array('status', $userColumns, true)) {
            $attributes['status'] = 'active';
        }

        $user = User::create($attributes);

        if (in_array('user_id', $cryptoColumns, true)) {
            $cryptoaccnt = new CryptoAccount();
            $cryptoaccnt->user_id = $user->id;
            foreach (['btc', 'eth', 'ltc', 'xrp', 'link', 'bat', 'aave', 'usdt', 'xlm', 'bch'] as $coin) {
                if (in_array($coin, $cryptoColumns, true)) {
                    $cryptoaccnt->$coin = 0;
                }
            }
            $cryptoaccnt->save();
        }
        $request->session()->forget('ref_by');

        try {
            // Send welcome email
            Mail::to($user->email)->send(new WelcomeEmail($user));
        } catch (\Exception $e) {
            Log::error('Failed to send welcome email to new user', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'user_email' => $user->email,
                'user_name' => $user->name
            ]);
        }

        try {
            // Send welcome notification
            $this->notificationService->sendWelcomeNotification($user->id, $user->name);
        } catch (\Exception $e) {
            Log::error('Failed to send welcome notification to new user', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'user_name' => $user->name
            ]);
        }

        return $user;
    }
}
