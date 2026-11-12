<div class="space-y-4 text-xs text-navy">
    {{-- Header Status --}}
    <div class="p-3.5 rounded-xl bg-sky-soft/80 border border-brand/20 flex items-center justify-between">
        <div>
            <h4 class="font-black text-sm text-navy">Tahun Ajaran: {{ $preview['target_year']->name }}</h4>
            <p class="text-ink-mute text-[11px] mt-0.5">
                Tahun Ajaran Berjalan Saat Ini: <span class="font-bold text-navy">{{ $preview['active_year']->name }}</span>
            </p>
        </div>
        <div class="text-right">
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-extrabold {{ $preview['can_activate'] ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                {{ $preview['can_activate'] ? 'Siap Diaktifkan' : 'Perlu Validasi' }}
            </span>
        </div>
    </div>

    {{-- Ringkasan Angka --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
        <div class="p-3 bg-white rounded-xl border border-sand/70 shadow-2xs">
            <span class="text-[10px] text-ink-mute uppercase font-bold tracking-wider block">Total Siswa Aktif Saat Ini</span>
            <span class="text-base font-black text-navy">{{ $preview['total_active_students'] }} Siswa</span>
        </div>
        <div class="p-3 bg-white rounded-xl border border-sand/70 shadow-2xs">
            <span class="text-[10px] text-ink-mute uppercase font-bold tracking-wider block">Sudah Dipetakan</span>
            <span class="text-base font-black text-emerald-700">{{ $preview['total_mapped'] }} Siswa</span>
        </div>
        <div class="p-3 bg-white rounded-xl border border-sand/70 shadow-2xs">
            <span class="text-[10px] text-ink-mute uppercase font-bold tracking-wider block">Belum Dipetakan</span>
            <span class="text-base font-black {{ $preview['unassigned_count'] > 0 ? 'text-amber-600' : 'text-emerald-700' }}">
                {{ $preview['unassigned_count'] }} Siswa
            </span>
        </div>
        <div class="p-3 bg-white rounded-xl border border-sand/70 shadow-2xs">
            <span class="text-[10px] text-ink-mute uppercase font-bold tracking-wider block">Data Ganda / Anomali</span>
            <span class="text-base font-black {{ $preview['duplicates_count'] > 0 ? 'text-rose-600' : 'text-emerald-700' }}">
                {{ $preview['duplicates_count'] }} Kasus
            </span>
        </div>
    </div>

    {{-- Rekap Rombel Baru --}}
    <div class="space-y-1.5">
        <h5 class="font-black text-xs text-navy uppercase tracking-wider">1. Rencana Jumlah Siswa Per Rombel Baru</h5>
        @if (!empty($preview['rombel_summary']))
            <div class="overflow-x-auto rounded-xl border border-sand/70 bg-white">
                <table class="w-full text-left text-xs">
                    <thead class="bg-sky-soft text-navy font-bold border-b border-sand/60">
                        <tr>
                            <th class="p-2.5">Rombel Tujuan</th>
                            <th class="p-2.5 text-center">Laki-Laki (L)</th>
                            <th class="p-2.5 text-center">Perempuan (P)</th>
                            <th class="p-2.5 text-center">Total Siswa</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-sand/40 font-medium">
                        @foreach ($preview['rombel_summary'] as $rombel)
                            <tr class="hover:bg-cream/40">
                                <td class="p-2.5 font-bold text-navy">{{ $rombel['class_name'] }}</td>
                                <td class="p-2.5 text-center text-ink-soft">{{ $rombel['male'] }}</td>
                                <td class="p-2.5 text-center text-ink-soft">{{ $rombel['female'] }}</td>
                                <td class="p-2.5 text-center font-black text-navy">{{ $rombel['total'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-center text-xs">
                Belum ada rombel tujuan yang dipetakan pada tahun ajaran ini. Silakan gunakan tombol <strong>Atur Kenaikan Kelas</strong>.
            </div>
        @endif
    </div>

    {{-- Pengecualian Siswa --}}
    <div class="space-y-1.5">
        <h5 class="font-black text-xs text-navy uppercase tracking-wider">2. Siswa Pengecualian (Tinggal Kelas, Pindah Sekolah, Lulus)</h5>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
            <div class="p-2.5 rounded-xl bg-amber-50/70 border border-amber-200">
                <span class="font-bold text-amber-900 block">Tinggal Kelas</span>
                <span class="text-sm font-black text-amber-800">{{ count($preview['exceptions']['tinggal_kelas']) }} Siswa</span>
            </div>
            <div class="p-2.5 rounded-xl bg-rose-50/70 border border-rose-200">
                <span class="font-bold text-rose-900 block">Pindah / Mutasi Keluar</span>
                <span class="text-sm font-black text-rose-800">{{ count($preview['exceptions']['mutasi_keluar']) }} Siswa</span>
            </div>
            <div class="p-2.5 rounded-xl bg-blue-50/70 border border-blue-200">
                <span class="font-bold text-blue-900 block">Lulus (Calon Alumni)</span>
                <span class="text-sm font-black text-blue-800">{{ count($preview['exceptions']['lulus']) }} Siswa</span>
            </div>
        </div>
    </div>

    {{-- Peringatan Siswa Belum Dipetakan --}}
    @if ($preview['unassigned_count'] > 0)
        <div class="p-3 rounded-xl bg-amber-50 border border-amber-300 text-amber-900 space-y-1">
            <div class="flex items-center gap-2 font-bold text-xs">
                <svg class="w-4 h-4 text-amber-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>Perhatian: Masih ada {{ $preview['unassigned_count'] }} siswa aktif yang belum dipetakan.</span>
            </div>
            <p class="text-[11px] text-amber-800">
                Pastikan seluruh rombel (7A sampai 7G, 8A sampai 8G, dan 9A sampai 9F) sudah dipetakan sebelum mengaktifkan tahun ajaran baru.
            </p>
        </div>
    @endif
</div>

