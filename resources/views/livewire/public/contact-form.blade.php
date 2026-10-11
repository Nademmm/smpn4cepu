<div>
    <x-card class="space-y-4 p-5 sm:p-6 shadow-xs">
        <div class="flex items-center justify-between pb-3 border-b-2 border-sun-soft">
            <div>
                <h3 class="font-extrabold text-navy text-base">
                    Kirim Pesan / Pertanyaan
                </h3>
                <p class="text-[11px] text-ink-soft">Pesan akan langsung terhubung ke layanan Tata Usaha dan Humas sekolah.</p>
            </div>
            <span class="text-xs text-ink-mute font-bold bg-sky-soft text-brand px-3 py-1 rounded-full border border-brand/20">Layanan Humas</span>
        </div>

        {{-- Feedback Sukses --}}
        @if ($submitted)
            <div class="p-4 rounded-2xl bg-emerald-50 border-2 border-emerald-300 text-emerald-900 text-xs font-bold flex items-center justify-between gap-3 shadow-xs animate-fade-in">
                <div class="flex items-center gap-2">
                    <span class="text-base">✅</span>
                    <span>Terima kasih! Pesan Anda telah berhasil tersimpan dan akan segera ditindaklanjuti oleh staf sekolah.</span>
                </div>
                <button type="button" wire:click="resetForm" class="text-emerald-700 hover:text-emerald-950 font-extrabold text-xs underline shrink-0">
                    Kirim Pesan Lain
                </button>
            </div>
        @endif

        <form wire:submit="submitMessage" class="space-y-3.5">
            {{-- Honeypot bot protection (tersembunyi) --}}
            <div class="hidden" aria-hidden="true">
                <input type="text" wire:model="honey" tabindex="-1" autocomplete="off">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div>
                    <label class="block text-xs font-bold text-navy mb-1.5">Nama Lengkap <span class="text-rose-500">*</span></label>
                    <input
                        type="text"
                        wire:model="name"
                        placeholder="Nama Anda..."
                        class="field @error('name') !border-rose-400 !bg-rose-50/30 @enderror"
                    >
                    @error('name')
                        <span class="text-[11px] font-bold text-rose-600 block mt-1">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label class="block text-xs font-bold text-navy mb-1.5">Email / No. Telepon WhatsApp <span class="text-rose-500">*</span></label>
                    <input
                        type="text"
                        wire:model="email_or_phone"
                        placeholder="kontak@example.com / 0812..."
                        class="field @error('email_or_phone') !border-rose-400 !bg-rose-50/30 @enderror"
                    >
                    @error('email_or_phone')
                        <span class="text-[11px] font-bold text-rose-600 block mt-1">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-navy mb-1.5">Perihal / Topik <span class="text-rose-500">*</span></label>
                <input
                    type="text"
                    wire:model="subject"
                    placeholder="Keperluan informasi, PPDB, legalisir ijazah, atau saran..."
                    class="field @error('subject') !border-rose-400 !bg-rose-50/30 @enderror"
                >
                @error('subject')
                    <span class="text-[11px] font-bold text-rose-600 block mt-1">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-navy mb-1.5">Pesan Lengkap <span class="text-rose-500">*</span></label>
                <textarea
                    rows="4"
                    wire:model="message"
                    placeholder="Tuliskan isi pesan atau pertanyaan Anda secara lengkap dan jelas..."
                    class="field @error('message') !border-rose-400 !bg-rose-50/30 @enderror"
                ></textarea>
                @error('message')
                    <span class="text-[11px] font-bold text-rose-600 block mt-1">{{ $message }}</span>
                @enderror
            </div>

            <div class="flex items-center justify-between pt-1">
                <span class="text-[11px] text-ink-mute font-medium flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    Semua kolom bertanda bintang wajib diisi
                </span>
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    class="btn-primary text-xs !min-h-[40px] px-6 shadow-2xs flex items-center gap-2"
                >
                    <span wire:loading.remove>Kirim Pesan Sekarang</span>
                    <span wire:loading class="flex items-center gap-2">
                        <span class="animate-spin text-sm">⏳</span>
                        Mengirim...
                    </span>
                    <x-app-icon name="arrow-right" class="w-3.5 h-3.5" wire:loading.remove />
                </button>
            </div>
        </form>
    </x-card>
</div>
