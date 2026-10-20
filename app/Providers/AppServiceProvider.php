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

        // 1. Optimasi koneksi SQLite (WAL mode & memory temp store untuk performa I/O maksimal)
        try {
            if (config('database.default') === 'sqlite') {
                \Illuminate\Support\Facades\DB::statement('PRAGMA journal_mode = WAL;');
                \Illuminate\Support\Facades\DB::statement('PRAGMA synchronous = NORMAL;');
                \Illuminate\Support\Facades\DB::statement('PRAGMA temp_store = MEMORY;');
                \Illuminate\Support\Facades\DB::statement('PRAGMA cache_size = -20000;');
            }
        } catch (\Throwable $e) {
            // Abaikan bila database belum siap
        }

        // 2. Bagikan SchoolSetting ke tampilan publik Blade menggunakan cache singleton
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
