<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AdminPaginationServiceProvider extends ServiceProvider
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
        // Set default pagination view for admin routes
        if (request()->is('admin/*')) {
            Paginator::defaultView('admin.pagination.bootstrap-4');
            Paginator::defaultSimpleView('admin.pagination.bootstrap-4-simple');
        }
    }
}
