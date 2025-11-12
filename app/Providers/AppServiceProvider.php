<?php

namespace App\Providers;

use App\Auth\CustomUserProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use App\Models\Holiday;
use App\Models\MasterApplication;
use App\Models\MasterNonProjectType;
use App\Models\MasterProjectStatus;
use App\Models\User as AppUser;

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
        Auth::provider('custom', function ($app, $config) {
            return new CustomUserProvider($app['hash'], $config['model']);
        });

        // Deny access to 'Master' models for non-admin users. Admins (is_admin=true)
        // retain full access. This prevents non-admins from reaching Master pages
        // even if they guess the URL.
        Gate::before(function ($user, $ability, $arguments) {
            if (! $user) {
                return null; // let other auth rules handle guest
            }

            // Admin users allowed
            if (($user->is_admin ?? false)) {
                return null;
            }

            $model = null;
            if (isset($arguments[0]) && is_string($arguments[0]) && class_exists($arguments[0])) {
                $model = $arguments[0];
            } elseif (isset($arguments[0]) && is_object($arguments[0])) {
                $model = get_class($arguments[0]);
            }

            $masterModels = [
                AppUser::class,
                Holiday::class,
                MasterProjectStatus::class,
                MasterApplication::class,
                MasterNonProjectType::class,
            ];

            if ($model && in_array($model, $masterModels, true)) {
                return false; // explicitly deny
            }

            return null;
        });
    }
}
