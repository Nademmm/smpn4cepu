<div class="space-y-4 text-sm text-gray-700 dark:text-gray-300">
    <div class="p-3 bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-900 rounded-xl">
        <p class="font-semibold text-blue-900 dark:text-blue-200 text-xs uppercase tracking-wider mb-1">
            Prinsip Pemulihan Sistem
        </p>
        <p class="text-xs text-blue-800 dark:text-blue-300">
            Berkas backup yang dihasilkan mencakup salinan basis data SQLite dan seluruh berkas fisik yang diunggah (materi PDF, e-book, cover buku, dan foto). Pemulihan wajib mengembalikan kedua komponen ini secara bersamaan agar referensi data tidak terputus.
        </p>
    </div>

    <div class="space-y-3">
        <div class="border border-gray-200 dark:border-gray-800 rounded-xl p-3 bg-white dark:bg-gray-900">
            <h4 class="font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-brand/10 text-brand text-xs flex items-center justify-center font-bold">1</span>
                Unduh dan Ekstrak Berkas Arsip (.zip)
            </h4>
            <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                Unduh berkas cadangan terbaru dari daftar tabel di bawah ke komputer atau server tujuan, lalu ekstrak arsip ZIP tersebut. Di dalamnya terdapat folder <code class="text-xs bg-gray-100 dark:bg-gray-800 px-1 py-0.5 rounded">database/</code>, <code class="text-xs bg-gray-100 dark:bg-gray-800 px-1 py-0.5 rounded">storage_public/</code>, dan <code class="text-xs bg-gray-100 dark:bg-gray-800 px-1 py-0.5 rounded">manifest.json</code>.
            </p>
        </div>

        <div class="border border-gray-200 dark:border-gray-800 rounded-xl p-3 bg-white dark:bg-gray-900">
            <h4 class="font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-brand/10 text-brand text-xs flex items-center justify-center font-bold">2</span>
                Pemulihan Basis Data (Database SQLite)
            </h4>
            <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                Salin berkas <code class="text-xs bg-gray-100 dark:bg-gray-800 px-1 py-0.5 rounded">database/database.sqlite</code> dari hasil ekstraksi ke folder proyek <code class="text-xs bg-gray-100 dark:bg-gray-800 px-1 py-0.5 rounded">database/database.sqlite</code> di server tujuan. Pastikan berkas memiliki izin tulis (write permission) untuk user web server (misal: www-data).
            </p>
        </div>

        <div class="border border-gray-200 dark:border-gray-800 rounded-xl p-3 bg-white dark:bg-gray-900">
            <h4 class="font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-brand/10 text-brand text-xs flex items-center justify-center font-bold">3</span>
                Pemulihan Berkas Unggahan Publik
            </h4>
            <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                Salin seluruh isi folder <code class="text-xs bg-gray-100 dark:bg-gray-800 px-1 py-0.5 rounded">storage_public/</code> ke dalam direktori <code class="text-xs bg-gray-100 dark:bg-gray-800 px-1 py-0.5 rounded">storage/app/public/</code> di server tujuan.
            </p>
        </div>

        <div class="border border-gray-200 dark:border-gray-800 rounded-xl p-3 bg-white dark:bg-gray-900">
            <h4 class="font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-brand/10 text-brand text-xs flex items-center justify-center font-bold">4</span>
                Tautan Simbolik dan Migrasi Struktur
            </h4>
            <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                Jalankan perintah berikut di terminal server untuk menghubungkan berkas publik dan memastikan struktur skema termutakhir:
            </p>
            <div class="mt-2 p-2 bg-gray-900 text-gray-100 rounded-lg font-mono text-xs space-y-1">
                <div>php artisan storage:link</div>
                <div>php artisan migrate --force</div>
                <div>php artisan optimize:clear</div>
            </div>
        </div>

        <div class="border border-gray-200 dark:border-gray-800 rounded-xl p-3 bg-white dark:bg-gray-900">
            <h4 class="font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-brand/10 text-brand text-xs flex items-center justify-center font-bold">5</span>
                Verifikasi dan Uji Keterbacaan
            </h4>
            <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                Buka portal publik dan panel admin, periksa materi PDF di Ruang Belajar, cover buku di Perpustakaan Digital, dan lakukan uji login menggunakan akun Super Admin.
            </p>
        </div>
    </div>
</div>

