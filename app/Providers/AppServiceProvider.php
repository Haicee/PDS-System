<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View; // <-- import View
use Illuminate\Support\Facades\Auth; // <-- import Auth
use App\Models\PdsRejection;

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
        $hasSubmitted = false;
        $hasRejected = false;

        if ($user && $user->role === 'employee') {
            $hasSubmitted = $user->hasSubmittedPds();
            $hasRejected = PdsRejection::where('user_id', $user->id)->exists();
        }

        $view->with('hasSubmittedPds', $hasSubmitted);
        $view->with('hasRejectedPds', $hasRejected);
    });
    }
}
