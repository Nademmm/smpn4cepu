<x-layouts.app title="Kontak & Lokasi Sekolah" metaDescription="Alamat, nomor telepon, email, jam layanan, dan peta lokasi SMP Negeri 4 Cepu di Mulyorejo, Cepu, Blora. Kirim pesan lewat formulir kontak.">
    <x-page-header
        title="Hubungi Kami"
        subtitle="Informasi alamat, nomor telepon resmi, surel, peta lokasi, dan saluran komunikasi SMP Negeri 4 Cepu."
        crumb="Kontak"
    />

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 pb-16">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            {{-- Kolom Kiri: Informasi Kontak Resmi & Jam Layanan --}}
            <div class="lg:col-span-5 space-y-6">
                <x-card class="space-y-5">
                    <h2 class="text-xl font-extrabold text-navy pb-3 border-b-2 border-sun-soft">
                        Kantor Pelayanan Sekolah
                    </h2>

                    <div class="space-y-4 text-sm">
                        <div class="flex items-start gap-3">
                            <div class="w-9 h-9 rounded-xl bg-sky-soft text-brand flex items-center justify-center shrink-0">
                                <x-app-icon name="pin" class="w-5 h-5" />
                            </div>
                            <div>
                                <span class="text-xs font-bold text-ink-mute uppercase block">Alamat Resmi</span>
                                <p class="font-extrabold text-navy leading-snug">
                                    {{ $schoolSetting->school_address ?? 'Jl. Raya Cepu Randu Km. 3,5, Desa Mulyorejo, Kecamatan Cepu, Kabupaten Blora, Jawa Tengah 58315' }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <div class="w-9 h-9 rounded-xl bg-sky-soft text-brand flex items-center justify-center shrink-0">
                                <x-app-icon name="phone" class="w-5 h-5" />
                            </div>
                            <div>
                                <span class="text-xs font-bold text-ink-mute uppercase block">Telepon Kantor</span>
                                <p class="font-extrabold text-navy leading-snug">
                                    {{ $schoolSetting->school_phone ?? '(0296) 421631' }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <div class="w-9 h-9 rounded-xl bg-sky-soft text-brand flex items-center justify-center shrink-0">
                                <x-app-icon name="mail" class="w-5 h-5" />
                            </div>
                            <div>
                                <span class="text-xs font-bold text-ink-mute uppercase block">Surat Elektronik</span>
                                <p class="font-extrabold text-navy leading-snug">
                                    {{ $schoolSetting->school_email ?? 'smpn4cepu@gmail.com' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 border-t-2 border-sun-soft">
                        <span class="text-xs font-bold text-ink-mute uppercase block mb-1">Jam Pelayanan Tata Usaha</span>
                        <p class="text-xs font-semibold text-navy leading-relaxed">
                            Senin - Kamis: 07.00 - 14.00 WIB<br>
                            Jumat: 07.00 - 11.00 WIB · Sabtu: 07.00 - 13.00 WIB
                        </p>
                    </div>
                </x-card>
            </div>

            {{-- Kolom Kanan: Peta Google Maps & Formulir Pesan --}}
            <div class="lg:col-span-7 space-y-6">
                {{-- Sematan Peta Interaktif --}}
                <x-card class="p-0 overflow-hidden shadow-xs">
                    <div class="p-4 bg-sky-soft border-b-2 border-sun-soft flex items-center justify-between">
                        <div>
                            <h3 class="font-extrabold text-navy text-sm">Peta Lokasi Satuan Pendidikan</h3>
                            <p class="text-[11px] text-ink-soft">Koordinat: -7.1576081, 111.5662531</p>
                        </div>
                        <span class="text-xs font-black text-brand bg-white px-3 py-1 rounded-full border border-sun-soft">
                            Google Maps
                        </span>
                    </div>
                    <iframe
                        src="{{ $schoolSetting?->maps_embed_url ?: 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3958.8256193796336!2d111.56367817574345!3d-7.157602770284422!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e776994781498b3%3A0x6a053cbfd38cce88!2sSMP%20Negeri%204%20Cepu!5e0!3m2!1sid!2sid!4v1700000000000!5m2!1sid!2sid' }}"
                        width="100%"
                        height="260"
                        style="border:0;"
                        allowfullscreen=""
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        title="Peta Lokasi SMP Negeri 4 Cepu"
                    ></iframe>
                </x-card>

                {{-- Formulir Pesan Livewire (Tersimpan ke Database) --}}
                <livewire:public.contact-form />
            </div>
        </div>
    </div>
</x-layouts.app>
