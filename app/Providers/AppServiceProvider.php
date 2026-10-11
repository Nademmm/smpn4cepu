<?php

namespace App\Providers;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
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

        // 1. Bagikan SchoolSetting ke seluruh tampilan publik Blade
        view()->composer('*', function ($view) {
            try {
                if (Schema::hasTable('school_settings')) {
                    $view->with('schoolSetting', \App\Models\SchoolSetting::current());
                }
            } catch (\Throwable $e) {
                // Abaikan jika migrasi belum berjalan
            }
        });

        // 2. Paksa penggunaan HTTPS di lingkungan produksi / PaaS
        if (config('app.env') === 'production' && !app()->runningInConsole()) {
            URL::forceScheme('https');
        }

        // 2. Otomatisasi migrasi dan seeder saat aplikasi pertama kali aktif di PaaS
        if (config('app.env') === 'production' && !app()->runningInConsole()) {
            try {
                if (!Schema::hasTable('users')) {
                    Artisan::call('migrate', ['--force' => true]);
                    Artisan::call('storage:link');

                    if (env('RUN_SEEDER', false)) {
                        Artisan::call('db:seed', ['--force' => true]);
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('PaaS bootstrap auto-migration skipped: ' . $e->getMessage());
            }
        }
    }
}
