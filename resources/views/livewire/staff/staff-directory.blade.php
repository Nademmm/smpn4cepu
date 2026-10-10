<div>
    {{-- Header Sesuai Desain Figma --}}
    <x-page-header
        title="Guru & Tenaga Kependidikan"
        subtitle="Tenaga pendidik profesional dan berdedikasi di SMP Negeri 4 Cepu"
        crumb="Guru & Tendik"
    />

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 pb-16 space-y-8">
        {{-- Pencarian & Filter Chip --}}
        <div class="flex flex-col md:flex-row gap-4 items-stretch md:items-end justify-between">
            <div class="space-y-2">
                <span class="text-xs font-bold uppercase tracking-wider text-ink-mute">STATUS KEPEGAWAIAN</span>
                <div class="flex flex-wrap gap-2">
                    <button
                        type="button"
                        wire:click="$set('statusFilter', '')"
                        class="chip {{ $statusFilter === '' ? 'chip-active' : '' }}"
                    >
                        Semua
                    </button>
                    @foreach ($statuses as $val => $lbl)
                        <button
                            type="button"
                            wire:click="$set('statusFilter', '{{ $val }}')"
                            class="chip {{ $statusFilter === $val ? 'chip-active' : '' }}"
                        >
                            {{ $lbl }}
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="w-full md:w-72">
                <span class="text-xs font-bold uppercase tracking-wider text-ink-mute block mb-2">CARI GURU / TENDIK</span>
                <div class="relative">
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Nama, NIP, atau mata pelajaran..."
                        class="field pl-10"
                    >
                    <x-app-icon name="search" class="w-4 h-4 text-ink-mute absolute left-3.5 top-1/2 -translate-y-1/2" />
                </div>
            </div>
        </div>

        {{-- Grid Kartu Guru & Tendik (Sesuai Desain Figma) --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
            @forelse ($staffMembers as $staff)
                <x-card class="text-center p-6 flex flex-col justify-between items-center h-full hover:border-brand transition-all group">
                    <div class="flex flex-col items-center w-full">
                        @if ($staff->photo_url)
                            <img
                                src="{{ $staff->photo_url }}"
                                alt="{{ $staff->name }}"
                                class="w-20 h-20 rounded-2xl object-cover border-2 border-sun-soft mb-3.5 group-hover:scale-105 transition-transform duration-200 shadow-2xs"
                            >
                        @else
                            @php
                                $words = explode(' ', trim($staff->name));
                                $initials = strtoupper(substr($words[0] ?? 'G', 0, 1) . substr($words[1] ?? '', 0, 1));
                            @endphp
                            <div class="w-20 h-20 rounded-2xl bg-brand text-white flex items-center justify-center font-black text-xl shadow-2xs mb-3.5 border-2 border-sun-soft group-hover:scale-105 transition-transform duration-200">
                                {{ $initials }}
                            </div>
                        @endif

                        <h3 class="font-extrabold text-navy text-[15px] sm:text-base leading-snug mb-1 group-hover:text-brand transition-colors">
                            {{ $staff->name }}
                        </h3>
                        <div class="text-xs font-bold text-brand mb-1">
                            {{ $staff->position }}
                        </div>
                        @if ($staff->nip)
                            <div class="text-[11px] font-semibold text-ink-mute">
                                NIP. {{ $staff->nip }}
                            </div>
                        @endif
                    </div>

                    <div class="mt-4 pt-3 border-t border-sun-soft/60 w-full flex justify-center">
                        <x-badge type="{{ $staff->employment_status->value === 'pns' ? 'blue' : ($staff->employment_status->value === 'pppk' ? 'green' : 'orange') }}">
                            {{ $staff->employment_status->label() }}
                        </x-badge>
                    </div>
                </x-card>
            @empty
                <div class="col-span-full">
                    <x-empty-state
                        title="Data Tidak Ditemukan"
                        text="Tidak ada tenaga pendidik atau kependidikan yang sesuai dengan kriteria filter Anda."
                    />
                </div>
            @endforelse
        </div>
    </div>
</div>
