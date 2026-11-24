<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Settings;
use App\Models\Paystack;
use App\Models\SettingsCont;
use Illuminate\Support\Facades\Schema;

class SettingsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Settings may not exist yet during installation, test startup, or a
        // partial database restore. Never let an empty settings table take
        // down every web/console request.
        $settings = null;
        $settings2 = null;
        try {
            if (Schema::hasTable('settings')) {
                $settings = Settings::where('id', 1)->first();
            }
            if (Schema::hasTable('settings_conts')) {
                $settings2 = SettingsCont::find(1);
            }
        } catch (\Throwable $e) {
            // Use application defaults until the database is available.
        }
        $settings = $settings ?: new Settings();
        $settings2 = $settings2 ?: new SettingsCont();

        // Temporarily commented out to allow bootstrap - uncomment when paystacks table is created
        // $paystack = Paystack::where('id', '1')->first();
        $paystack = null; // Temporary fallback
        if ($settings->install_type == 'Sub-Folder' && $settings->site_address) {
            $urls = explode('/', trim($settings->site_address, '/'));
            $assetUrl = '/' . end($urls);
        } else {
            $assetUrl = null;
        }

        // Set configuration values at run time
        config([
            'captcha.secret' => $settings->capt_secret ?: config('captcha.secret'),
            'captcha.sitekey' => $settings->capt_sitekey ?: config('captcha.sitekey'),
            'services.google.client_id' => $settings->google_id ?: config('services.google.client_id'),
            'services.google.client_secret' => $settings->google_secret ?: config('services.google.client_secret'),
            'services.google.redirect' => $settings->google_redirect ?: config('services.google.redirect'),
            'mail.mailers.smtp.host' => $settings->smtp_host ?: config('mail.mailers.smtp.host'),
            'mail.mailers.smtp.port' => $settings->smtp_port ?: config('mail.mailers.smtp.port'),
            'mail.mailers.smtp.encryption' => $settings->smtp_encrypt ?: config('mail.mailers.smtp.encryption'),
            'mail.mailers.smtp.username' => $settings->smtp_user ?: config('mail.mailers.smtp.username'),
            'mail.mailers.smtp.password' => $settings->smtp_password ?: config('mail.mailers.smtp.password'),
            'mail.default' => $settings->mail_server ?: config('mail.default'),
            'mail.from.address' => $settings->emailfrom ?: config('mail.from.address'),
            'mail.from.name' => $settings->emailfromname ?: config('mail.from.name'),
            'app.timezone' => $settings->timezone ?: config('app.timezone', 'UTC'),
            'app.name' => $settings->site_name ?: config('app.name', 'Laravel'),
            'app.url' => $settings->site_address ?: config('app.url'),
            'paystack.publicKey' => $paystack ? $paystack->paystack_public_key : '',
            'paystack.secretKey' => $paystack ? $paystack->paystack_secret_key : '',
            'paystack.paymentUrl' => $paystack ? $paystack->paystack_url : '',
            'paystack.merchantEmail' => $paystack ? $paystack->paystack_email : '',
            'livewire.asset_url' => $assetUrl,
            'flutterwave.publicKey' => $settings2->flw_public_key ?: '',
            'flutterwave.secretKey' => $settings2->flw_secret_key ?: '',
            'flutterwave.secretHash' => $settings2->flw_secret_hash ?: '',
            'services.telegram-bot-api.token' => $settings2->telegram_bot_api ?: '',
        ]);
    }
}
