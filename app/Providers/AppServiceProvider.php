<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View; // <-- import View
use Illuminate\Support\Facades\Auth; // <-- import Auth

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
   public function boot(): void
{
    // Make $hasSubmittedPds available in all Blade views for employees only
    View::composer('*', function ($view) {
        $user = Auth::user();

        // Check if the user is logged in AND is an employee
        if ($user && $user->role === 'employee') {
            $view->with('hasSubmittedPds', $user->hasSubmittedPds());
        } else {
            $view->with('hasSubmittedPds', false);
        }
    });
    }
}
