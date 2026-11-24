<?php

namespace App\Providers;

use App\Actions\Jetstream\DeleteUser;
use Illuminate\Support\ServiceProvider;
use Laravel\Jetstream\Jetstream;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Fortify;
use Illuminate\Support\Facades\DB;
use App\Models\Settings;
use App\Models\Activity;
use Jenssegers\Agent\Agent;
use Illuminate\Support\Facades\Schema;

class JetstreamServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->configurePermissions();
        Jetstream::deleteUsersUsing(DeleteUser::class);

        Fortify::loginView(function () {
            $settings = new Settings();
            try {
                if (Schema::hasTable('settings')) {
                    $settings = Settings::where('id', 1)->first() ?: $settings;
                }
            } catch (\Throwable $e) {
                // Render the login form with safe defaults during recovery.
            }

            return view('auth.login',[
                'title' => 'Sign In to Continue',
                'settings' => $settings,
                'supportsUsername' => User::hasUsernameColumn(),
            ]);
        });


        Fortify::authenticateUsing(function (Request $request) {
            $user = User::findByLoginIdentifier(trim((string) $request->input('email')));
            $agent = new Agent();

            if ($user &&
                Hash::check($request->password, $user->password)) {
                $request->session()->put('getAnouc', 'true');
                DB::table('activities')->insert([
                    'user' => $user->id,
                    'ip_address' => $request->ip(),
                    'device'=> $agent->device(),
                    'browser'=> $agent->browser(),
                    'os'=> $agent->platform(),
                ]);
                return $user;
            }
        });
        

        Fortify::registerView(function () {
            include 'get_user_ip_info.php';        
            $settings = new Settings();
            try {
                if (Schema::hasTable('settings')) {
                    $settings = Settings::where('id', 1)->first() ?: $settings;
                }
            } catch (\Throwable $e) {
                // Render the registration form with safe defaults during recovery.
            }

            return view('auth.register', [
                'title' => 'Register an Account',
                'user_country'=>$user_country,
                'countries'=>$countries,
                'settings' => $settings,
                'supportsUsername' => User::hasUsernameColumn(),
            ]);
        });

    }

    /**
     * Configure the permissions that are available within the application.
     *
     * @return void
     */
    protected function configurePermissions()
    {
        Jetstream::defaultApiTokenPermissions(['read']);

        Jetstream::permissions([
            'create',
            'read',
            'update',
            'delete',
        ]);
    }
}
