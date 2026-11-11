<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Jadwal pembersihan otomatis tiket peminjaman yang kadaluarsa (setiap hari pukul 00:30)
Schedule::command('library:expire-stale-reservations')->dailyAt('00:30');
