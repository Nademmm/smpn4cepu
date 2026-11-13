<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Jadwal pembersihan otomatis tiket peminjaman yang kadaluarsa (setiap hari pukul 00:30)
Schedule::command('library:expire-stale-reservations')->dailyAt('00:30');

// Jadwal backup penuh harian (Database SQLite + Berkas Unggahan Publik) setiap pukul 02.00
Schedule::command('system:backup')->dailyAt('02:00');

// Jadwal pembersihan permanen data di Daftar Terhapus yang melewati retensi 30 hari (setiap pukul 03.00)
Schedule::command('system:prune-trash --days=30')->dailyAt('03:00');
