<x-layouts.app title="Galeri Foto Kegiatan">
    <x-page-header
        title="Galeri Kegiatan"
        subtitle="Dokumentasi aktivitas pembelajaran, ekstrakurikuler, dan momentum kebersamaan di SMP Negeri 4 Cepu."
        crumb="Galeri Foto"
    />

    @php
        $photos = [
            [
                'file' => '20260717_092659.jpg',
                'title' => 'Gedung Utama dan Halaman Sekolah',
                'caption' => 'Suasana gerbang dan gedung induk pembelajaran SMP Negeri 4 Cepu.',
                'tag' => 'Lingkungan Sekolah',
            ],
            [
                'file' => 'IMG_5797.JPG',
                'title' => 'Aktivitas Belajar dan Keakraban Siswa',
                'caption' => 'Keseruan proses belajar dan interaksi antarsiswa di lingkungan sekolah.',
                'tag' => 'Kesiswaan',
            ],
            [
                'file' => 'IMG_6518.JPG',
                'title' => 'Kegiatan Pembelajaran dan Kreativitas',
                'caption' => 'Siswa mengeksplorasi bakat serta keterampilan melalui kegiatan aplikatif.',
                'tag' => 'Pembelajaran',
            ],
            [
                'file' => 'IMG_6522.JPG',
                'title' => 'Kebersamaan Siswa dan Pendidik',
                'caption' => 'Interaksi hangat antara guru dan peserta didik dalam suasana santai.',
                'tag' => 'Warga Sekolah',
            ],
            [
                'file' => 'IMG_8383.JPG',
                'title' => 'Dokumentasi Program Sekolah',
                'caption' => 'Momentum pelaksanaan agenda penting satuan pendidikan.',
                'tag' => 'Agenda',
            ],
        ];
    @endphp

    <div
        class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 pb-16"
        x-data="{
            activeImage: null,
            activeTitle: '',
            activeCaption: '',
            openPreview(img, title, caption) {
                this.activeImage = img;
                this.activeTitle = title;
                this.activeCaption = caption;
            }
        }"
    >
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($photos as $p)
                <div
                    class="card p-0 overflow-hidden flex flex-col justify-between hover:border-brand transition-all group cursor-pointer shadow-xs"
                    @click="openPreview('{{ asset('images/assets/' . $p['file']) }}', '{{ addslashes($p['title']) }}', '{{ addslashes($p['caption']) }}')"
                >
                    <div>
                        <div class="relative overflow-hidden">
                            <img
                                src="{{ asset('images/assets/' . $p['file']) }}"
                                alt="{{ $p['title'] }}"
                                class="w-full h-56 object-cover group-hover:scale-105 transition-transform duration-300"
                            >
                            <span class="absolute top-3.5 left-3.5 bg-navy/85 backdrop-blur-xs text-white text-[11px] font-extrabold px-3 py-1 rounded-full border border-white/20">
                                {{ $p['tag'] }}
                            </span>
                            <div class="absolute inset-0 bg-navy/20 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center pointer-events-none">
                                <span class="bg-white/90 text-navy text-xs font-black px-3.5 py-1.5 rounded-full shadow-md flex items-center gap-1.5">
                                    <x-app-icon name="photo" class="w-3.5 h-3.5 text-brand" />
                                    <span>Perbesar</span>
                                </span>
                            </div>
                        </div>
                        <div class="p-5 sm:p-6">
                            <h3 class="font-extrabold text-navy text-base leading-snug mb-1.5 group-hover:text-brand transition-colors">
                                {{ $p['title'] }}
                            </h3>
                            <p class="text-xs sm:text-sm text-ink-soft leading-relaxed font-medium">
                                {{ $p['caption'] }}
                            </p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Modal Preview Foto --}}
        <div
            x-show="activeImage !== null"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-navy/80 backdrop-blur-xs"
        >
            <div
                @click.away="activeImage = null"
                class="card max-w-2xl w-full p-4 space-y-3"
            >
                <div class="flex items-center justify-between pb-2 border-b-2 border-sun-soft">
                    <h3 class="font-extrabold text-navy text-base" x-text="activeTitle"></h3>
                    <button type="button" @click="activeImage = null" class="p-1 rounded-lg text-ink-mute hover:text-navy">
                        <x-app-icon name="x" class="w-5 h-5" />
                    </button>
                </div>
                <img :src="activeImage" :alt="activeTitle" class="w-full max-h-[70vh] object-contain rounded-xl border border-sun-soft">
                <p class="text-xs text-ink-soft font-semibold" x-text="activeCaption"></p>
            </div>
        </div>
    </div>
</x-layouts.app>
