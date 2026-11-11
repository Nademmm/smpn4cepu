<?php

namespace App\Console\Commands;

use App\Services\LibraryLoanService;
use Illuminate\Console\Command;

class ExpireStaleBookReservationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'library:expire-stale-reservations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Otomatis menandai tiket reservasi buku yang tidak kunjung diambil sebagai kadaluarsa dan mengembalikan stok fisik';

    /**
     * Execute the console command.
     */
    public function handle(LibraryLoanService $loanService): int
    {
        $this->info('Memeriksa tiket reservasi yang melewati batas waktu pengambilan...');

        $count = $loanService->expireStale();

        if ($count > 0) {
            $this->info("Berhasil mengkadaluarsakan {$count} tiket yang tidak diambil dan melepaskan kembali stok buku ke katalog.");
        } else {
            $this->info('Tidak ada tiket yang kadaluarsa hari ini.');
        }

        return self::SUCCESS;
    }
}
