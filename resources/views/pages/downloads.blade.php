<x-layouts.app title="Pusat Unduhan Berkas">
    <x-page-header
        title="Pusat Unduhan Berkas"
        subtitle="Dokumen resmi, formulir administrasi kesiswaan, tata tertib, dan kalender akademik SMP Negeri 4 Cepu."
        crumb="Unduhan Berkas"
    />

    @php
        $downloads = [
            [
                'title' => 'Kalender Akademik Tahun Ajaran 2026/2027',
                'category' => 'Akademik',
                'format' => 'PDF',
                'size' => '1.2 MB',
                'description' => 'Jadwal masuk sekolah, penilaian tengah semester, asesmen akhir semester, serta hari libur resmi.',
            ],
            [
                'title' => 'Buku Saku Tata Tertib & Kode Etik Peserta Didik',
                'category' => 'Kesiswaan',
                'format' => 'PDF',
                'size' => '850 KB',
                'description' => 'Pedoman kedisiplinan, aturan seragam, hak dan kewajiban siswa, serta norma perilaku di lingkungan sekolah.',
            ],
            [
                'title' => 'Formulir Permohonan Izin / Keterangan Tidak Masuk Sekolah',
                'category' => 'Formulir',
                'format' => 'PDF',
                'size' => '320 KB',
                'description' => 'Surat permohonan resmi dari orang tua / wali murid untuk izin sakit atau keperluan mendesak keluarga.',
            ],
            [
                'title' => 'Panduan Peminjaman Buku Perpustakaan Sekolah',
                'category' => 'Perpustakaan',
                'format' => 'PDF',
                'size' => '540 KB',
                'description' => 'Alur dan tata cara peminjaman koleksi buku fisik di ruang perpustakaan SMP Negeri 4 Cepu.',
            ],
        ];
    @endphp

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 pb-16 space-y-4" x-data="{ notice: '' }">
        {{-- Banner Notifikasi Interaktif --}}
        <div x-show="notice !== ''" x-cloak class="p-4 rounded-2xl bg-sky-soft border-2 border-brand/30 text-navy text-xs font-bold flex items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <x-app-icon name="megaphone" class="w-4 h-4 text-brand shrink-0" />
                <span x-text="notice"></span>
            </div>
            <button type="button" @click="notice = ''" class="text-ink-mute hover:text-navy">
                <x-app-icon name="x" class="w-4 h-4" />
            </button>
        </div>

        @foreach ($downloads as $doc)
            <x-card class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-5 p-5 sm:p-6 hover:border-brand transition-all group">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-sky-soft text-brand flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                        <x-app-icon name="download" class="w-6 h-6" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2 mb-1.5">
                            <x-badge type="blue">{{ $doc['category'] }}</x-badge>
                            <span class="text-[11px] font-bold text-ink-mute uppercase tracking-wider">{{ $doc['format'] }} · {{ $doc['size'] }}</span>
                        </div>
                        <h2 class="font-extrabold text-navy text-base leading-snug group-hover:text-brand transition-colors">
                            {{ $doc['title'] }}
                        </h2>
                        <p class="text-xs text-ink-soft leading-relaxed mt-1 font-medium">
                            {{ $doc['description'] }}
                        </p>
                    </div>
                </div>

                <div class="shrink-0 w-full sm:w-auto">
                    <button
                        type="button"
                        @click="notice = 'Berkas digital ' + '{{ addslashes($doc['title']) }}' + ' sedang disiapkan oleh staf tata usaha.'"
                        class="btn-primary text-xs w-full sm:w-auto !min-h-[38px] shadow-2xs"
                    >
                        <x-app-icon name="download" class="w-4 h-4" />
                        Unduh Berkas
                    </button>
                </div>
            </x-card>
        @endforeach
    </div>
</x-layouts.app>
