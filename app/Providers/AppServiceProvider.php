<?php

namespace App\Providers;

use App\Models\Claim;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        View::composer('layouts.app', function ($view) {
            if (!Auth::check()) {
                $view->with('notificationCount', 0);
                return;
            }

            $user = Auth::user();

            $count = $user->isFinanceAdmin()
                ? Claim::where('is_admin_read', false)->count()
                : Claim::where('user_id', $user->id)->where('is_read', false)->count();

            $view->with('notificationCount', $count);
        });
    }
}
