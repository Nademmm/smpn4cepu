<div>
    {{-- Search & Dual Filter Controls --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 mb-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            {{-- Pencarian Nama / NIP --}}
            <div>
                <label for="search" class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">
                    Cari Nama atau NIP
                </label>
                <div class="relative">
                    <input type="text" id="search" wire:model.live.debounce.300ms="search" placeholder="Ketik nama atau NIP guru..."
                           class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </div>

            {{-- Filter Status Kepegawaian --}}
            <div>
                <label for="statusFilter" class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">
                    Status Kepegawaian
                </label>
                <select id="statusFilter" wire:model.live="statusFilter"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Status (PNS, PPPK, Honorer)</option>
                    @foreach($statuses as $val => $label)
                        <option value="{{ $val }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Filter Bidang / Jabatan --}}
            <div>
                <label for="positionFilter" class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">
                    Tipe Jabatan
                </label>
                <select id="positionFilter" wire:model.live="positionFilter"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Jabatan</option>
                    <option value="Kepala Sekolah">Kepala Sekolah</option>
                    <option value="Guru">Guru / Tenaga Pendidik</option>
                    <option value="Staf">Tenaga Kependidikan / Tata Usaha</option>
                    <option value="Perpustakaan">Perpustakaan</option>
                </select>
            </div>
        </div>

        <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
            <span>Ditemukan <strong>{{ $staffMembers->count() }}</strong> profil guru dan tenaga kependidikan</span>
            @if(!empty($search) || !empty($statusFilter) || !empty($positionFilter))
                <button type="button" wire:click="$set('search', ''); $set('statusFilter', ''); $set('positionFilter', '')" class="text-blue-600 dark:text-blue-400 font-semibold hover:underline">
                    Reset Filter
                </button>
            @endif
        </div>
    </div>

    {{-- Grid Direktori GTK --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @forelse($staffMembers as $staff)
            <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-md transition-shadow flex flex-col justify-between">
                <div>
                    {{-- Avatar Inisial atau Foto --}}
                    <div class="flex items-center gap-4 mb-4">
                        @if($staff->photo_path)
                            <img src="{{ asset('storage/' . $staff->photo_path) }}" alt="{{ $staff->name }}" class="w-14 h-14 rounded-2xl object-cover border border-slate-200 dark:border-slate-700">
                        @else
                            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-700 flex items-center justify-center text-white font-extrabold text-xl shadow-inner">
                                {{ strtoupper(substr($staff->name, 0, 1)) }}
                            </div>
                        @endif
                        <div class="min-w-0">
                            <span class="inline-block px-2.5 py-0.5 rounded-md text-[11px] font-bold tracking-wide uppercase 
                                {{ $staff->employment_status->value === 'PNS' ? 'bg-blue-50 text-blue-700 dark:bg-blue-950 dark:text-blue-300' : '' }}
                                {{ $staff->employment_status->value === 'PPPK' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300' : '' }}
                                {{ $staff->employment_status->value === 'Honorer' ? 'bg-amber-50 text-amber-700 dark:bg-amber-950 dark:text-amber-300' : '' }}">
                                {{ $staff->employment_status->value }}
                            </span>
                            <p class="text-xs text-slate-400 mt-1 truncate">No. Urut: #{{ $staff->display_order }}</p>
                        </div>
                    </div>

                    <h3 class="font-bold text-slate-900 dark:text-white text-base leading-snug mb-1">
                        {{ $staff->name }}
                    </h3>
                    <p class="text-sm text-blue-600 dark:text-blue-400 font-medium mb-3">
                        {{ $staff->position }}
                    </p>
                </div>

                <div class="pt-3 border-t border-slate-100 dark:border-slate-800 text-xs text-slate-500 dark:text-slate-400">
                    <span class="block">NIP:</span>
                    <span class="font-mono text-slate-700 dark:text-slate-300 font-medium">{{ $staff->nip ?: 'Belum Terdaftar' }}</span>
                </div>
            </div>
        @empty
            <div class="col-span-full py-16 text-center bg-white dark:bg-slate-900 rounded-2xl border border-dashed border-slate-300 dark:border-slate-800">
                <p class="text-slate-500 dark:text-slate-400 text-base">Tidak ada data guru atau tenaga kependidikan yang sesuai dengan kriteria pencarian.</p>
            </div>
        @endforelse
    </div>
</div>
