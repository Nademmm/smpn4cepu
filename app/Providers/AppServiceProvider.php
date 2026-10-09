<?php

namespace App\Providers;

use Illuminate\Support\Facades\Artisan;
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
        // 1. Paksa penggunaan HTTPS di lingkungan produksi / PaaS
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
