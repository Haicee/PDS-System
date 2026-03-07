<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View; // <-- import View
use Illuminate\Support\Facades\Auth; // <-- import Auth
use App\Models\PdsRejection;
use Illuminate\Support\Facades\URL;

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

        // In local/dev, generate URLs (including email verification) using the current host
        if (app()->environment('local')) {
            $host = request()->getSchemeAndHttpHost();
            if ($host) {
                URL::forceRootUrl($host);
                URL::forceScheme(request()->getScheme());
            }
        }

        // Force https when secure or production (ngrok)
        if (app()->environment('production') || request()->isSecure()) {
            URL::forceScheme('https');
        }
    }

}
