<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        // Globalna varijabla za sve view-e: broj nepročitanih notifikacija
        view()->composer('layouts.app', function (View $view) {
            if (auth()->check()) {
                $view->with('unreadNotificationsCount', auth()->user()->unreadNotifications()->count());
            } else {
                $view->with('unreadNotificationsCount', 0);
            }
        });
    }
}
