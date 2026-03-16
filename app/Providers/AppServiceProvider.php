<?php

namespace App\Providers;

use App\Policies\ProductPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Auth\Notifications\ResetPassword;
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
        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            return config('app.frontend_url') . "/password-reset/$token?email={$notifiable->getEmailForPasswordReset()}";
        });

        // JsonResource::withoutWrapping();

        // Gate::define('create-user', [UserPolicy::class, 'create']);
        // Gate::define('update-user', [UserPolicy::class, 'update']);
        // Gate::define('delete-user', [UserPolicy::class, 'delete']);
        //
        // Gate::define('create-product', [ProductPolicy::class, 'create']);
        // Gate::define('update-product', [UserPolicy::class, 'update']);
        // Gate::define('delete-product', [UserPolicy::class, 'delete']);
    }
}
