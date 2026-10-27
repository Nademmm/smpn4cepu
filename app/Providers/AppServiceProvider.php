<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
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
        // Berikan akses penuh secara implisit kepada super_admin untuk semua Gate & Policy
        Gate::before(function ($user, $ability) {
            return $user->hasRole('super_admin') ? true : null;
        });

        // 1. Bagikan SchoolSetting ke tampilan publik Blade menggunakan cache singleton
        view()->composer(['pages.*', 'components.layouts.*'], function ($view) {
            try {
                $view->with('schoolSetting', \App\Models\SchoolSetting::current());
            } catch (\Throwable $e) {
                // Abaikan jika tabel belum dimigrasi
            }
        });

        // 2. Paksa penggunaan HTTPS di lingkungan produksi / PaaS
        if (config('app.env') === 'production' || request()->header('x-forwarded-proto') === 'https') {
            URL::forceScheme('https');
        }
    }
}
