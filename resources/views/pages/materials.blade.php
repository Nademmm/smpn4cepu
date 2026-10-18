<x-layouts.app title="Materi Pembelajaran">
    <x-page-header
        title="Belajar"
        subtitle="Baca materi, pelajari modul ajar, dan perluas wawasan belajarmu secara mandiri."
        crumb="Materi Pelajaran"
        variant="violet"
    >
        <x-slot:tabs>
            <a href="{{ route('materials') }}" class="tab tab-active">
                Materi Pelajaran
            </a>
            <a href="{{ route('quizzes') }}" class="tab">
                Latihan Soal
            </a>
        </x-slot:tabs>
    </x-page-header>

    @php
        $gradeCounts = $materials->groupBy(fn ($m) => (string) $m->grade_level)->map->count();
        $countAll = $materials->count();
        $count7 = $gradeCounts['7'] ?? 0;
        $count8 = $gradeCounts['8'] ?? 0;
        $count9 = $gradeCounts['9'] ?? 0;
    @endphp

    <div
        class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 pb-16 space-y-8"
        x-data="{
            selectedGrade: 'all',
            searchQuery: '',
            activeMaterial: null,
            materialsList: @js($materials->map(fn($m) => ['id' => $m->id, 'grade' => (string) $m->grade_level, 'title' => $m->title, 'subject' => $m->subject?->name ?? ''])),
            openMaterial(mat) {
                this.activeMaterial = mat;
            },
            matches(grade, title, subject) {
                const matchesGrade = this.selectedGrade === 'all' || this.selectedGrade === String(grade);
                if (!matchesGrade) return false;
                if (!this.searchQuery.trim()) return true;
                const query = this.searchQuery.toLowerCase();
                return (title && title.toLowerCase().includes(query)) || (subject && subject.toLowerCase().includes(query));
            },
            get filteredCount() {
                return this.materialsList.filter(m => this.matches(m.grade, m.title, m.subject)).length;
            }
        }"
        x-effect="document.body.classList.toggle('overflow-hidden', activeMaterial !== null)"
    >
        {{-- Filter Tingkat Kelas & Pencarian Materi --}}
        <div class="flex flex-col md:flex-row gap-4 items-stretch md:items-end justify-between">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-ink-mute block mb-2">PILIH TINGKAT KELAS</span>
                <div class="flex flex-wrap items-center gap-2" role="tablist" aria-label="Filter Tingkat Kelas">
                    <button
                        type="button"
                        @click="selectedGrade = 'all'"
                        :class="selectedGrade === 'all' ? 'chip-active' : ''"
                        class="chip transition-all"
                    >
                        <span>Semua Kelas</span>
                        <span class="ml-1.5 px-2 py-0.5 rounded-full text-[11px] font-black" :class="selectedGrade === 'all' ? 'bg-white/20 text-white' : 'bg-cream text-navy'">
                            {{ $countAll }}
                        </span>
                    </button>
                    <button
                        type="button"
                        @click="selectedGrade = '7'"
                        :class="selectedGrade === '7' ? 'chip-active' : ''"
                        class="chip transition-all"
                    >
                        <span>Kelas VII (7)</span>
                        <span class="ml-1.5 px-2 py-0.5 rounded-full text-[11px] font-black" :class="selectedGrade === '7' ? 'bg-white/20 text-white' : 'bg-cream text-navy'">
                            {{ $count7 }}
                        </span>
                    </button>
                    <button
                        type="button"
                        @click="selectedGrade = '8'"
                        :class="selectedGrade === '8' ? 'chip-active' : ''"
                        class="chip transition-all"
                    >
                        <span>Kelas VIII (8)</span>
                        <span class="ml-1.5 px-2 py-0.5 rounded-full text-[11px] font-black" :class="selectedGrade === '8' ? 'bg-white/20 text-white' : 'bg-cream text-navy'">
                            {{ $count8 }}
                        </span>
                    </button>
                    <button
                        type="button"
                        @click="selectedGrade = '9'"
                        :class="selectedGrade === '9' ? 'chip-active' : ''"
                        class="chip transition-all"
                    >
                        <span>Kelas IX (9)</span>
                        <span class="ml-1.5 px-2 py-0.5 rounded-full text-[11px] font-black" :class="selectedGrade === '9' ? 'bg-white/20 text-white' : 'bg-cream text-navy'">
                            {{ $count9 }}
                        </span>
                    </button>
                </div>
            </div>

            <div class="w-full md:w-80">
                <label for="material-search" class="text-xs font-bold uppercase tracking-wider text-ink-mute block mb-2">CARI MODUL / MAPEL</label>
                <div class="relative">
                    <input
                        id="material-search"
                        type="search"
                        x-model="searchQuery"
                        placeholder="Cari mata pelajaran atau judul materi..."
                        aria-label="Cari materi pembelajaran"
                        class="field !py-2.5 pl-9 pr-4 text-xs font-bold"
                    >
                    <x-app-icon name="search" class="w-4 h-4 text-ink-mute absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" />
                </div>
            </div>
        </div>

        {{-- Grid Materi Ajar --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @forelse ($materials as $index => $mat)
                @php
                    $matPayload = [
                        'title' => $mat->title,
                        'subject' => $mat->subject?->name ?? 'Mata Pelajaran',
                        'grade_level' => $mat->grade_level,
                        'author' => $mat->author?->name ?? 'Guru Pengampu',
                        'summary' => $mat->summary ?? '',
                        'content' => $mat->content ?: ($mat->summary ?? 'Modul pembelajaran Kurikulum Merdeka yang disusun oleh tenaga pendidik SMP Negeri 4 Cepu.'),
                        'attachment_url' => $mat->attachment_url,
                    ];
                @endphp
                <div
                    x-show="matches('{{ $mat->grade_level }}', @js($mat->title), @js($mat->subject?->name ?? ''))"
                    x-transition
                    class="h-full"
                >
                    <x-card class="h-full flex flex-col justify-between hover:border-brand transition-all p-5 sm:p-6 group">
                        <div>
                            <div class="flex items-center justify-between gap-2 mb-3">
                                <span class="text-xs font-black text-brand uppercase tracking-wider">
                                    {{ $mat->subject?->name ?? 'Mata Pelajaran' }}
                                </span>
                                <x-badge type="blue">Kelas {{ $mat->grade_level }}</x-badge>
                            </div>
                            <h3 class="font-extrabold text-navy text-[17px] mb-2 leading-snug group-hover:text-brand transition-colors">
                                <button
                                    type="button"
                                    @click="openMaterial(@js($matPayload))"
                                    class="text-left hover:underline focus:outline-none"
                                >
                                    {{ $mat->title }}
                                </button>
                            </h3>
                            <p class="text-xs text-ink-soft leading-relaxed line-clamp-3 mb-4 font-medium">
                                {{ $mat->summary ?? 'Modul pembelajaran Kurikulum Merdeka yang disusun oleh tenaga pendidik SMP Negeri 4 Cepu.' }}
                            </p>
                        </div>

                        <div class="pt-3.5 border-t border-sun-soft flex flex-wrap items-center justify-between gap-2">
                            <div class="flex items-center gap-1.5 text-xs text-ink-mute font-semibold">
                                <x-app-icon name="user" class="w-3.5 h-3.5 text-brand" />
                                <span class="line-clamp-1">{{ $mat->author?->name ?? 'Guru Pengampu' }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    @click="openMaterial(@js($matPayload))"
                                    class="btn-ghost text-xs !px-3.5"
                                >
                                    <x-app-icon name="book" class="w-3.5 h-3.5 text-brand" />
                                    <span>Baca Materi</span>
                                </button>
                                @if ($mat->attachment_url)
                                    <a
                                        href="{{ $mat->attachment_url }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="btn-primary text-xs !px-3.5 shadow-2xs"
                                    >
                                        Unduh Modul
                                    </a>
                                @endif
                            </div>
                        </div>
                    </x-card>
                </div>
            @empty
                <div class="col-span-full">
                    <x-empty-state
                        title="Materi Belum Tersedia"
                        text="Modul pembelajaran mandiri akan segera diunggah oleh guru pengampu mata pelajaran."
                    />
                </div>
            @endforelse

            @if ($materials->isNotEmpty())
                <div
                    x-show="filteredCount === 0"
                    x-cloak
                    class="col-span-full"
                >
                    <x-card class="text-center py-10 space-y-3">
                        <p class="text-base font-extrabold text-navy">Tidak Ada Materi yang Sesuai</p>
                        <p class="mx-auto max-w-md text-sm text-ink-soft">
                            Tidak ditemukan materi pembelajaran untuk kriteria pencarian atau tingkat kelas yang dipilih.
                        </p>
                        <div class="pt-2 flex justify-center gap-2">
                            <button
                                type="button"
                                @click="selectedGrade = 'all'; searchQuery = ''"
                                class="btn-primary text-xs"
                            >
                                Tampilkan Semua Materi
                            </button>
                        </div>
                    </x-card>
                </div>
            @endif
        </div>

        {{-- Modal Pembaca Materi ("Baca Materi") --}}
        <template x-teleport="body">
            <div
                x-show="activeMaterial !== null"
                x-cloak
                role="dialog"
                aria-modal="true"
                aria-labelledby="material-modal-title"
                @keydown.escape.window="activeMaterial = null"
                class="fixed inset-0 z-[100] w-screen h-screen min-h-screen flex items-center justify-center p-4 bg-navy/80 backdrop-blur-xs overflow-y-auto"
            >
                <div
                    @click.away="activeMaterial = null"
                    class="card max-w-2xl w-full p-0 overflow-hidden shadow-2xl max-h-[90vh] flex flex-col bg-white"
                >
                    <div class="p-5 bg-sky-soft border-b-2 border-sun-soft flex items-start justify-between gap-4">
                        <div class="flex-1">
                            <div class="flex flex-wrap items-center gap-2 mb-1">
                                <span class="text-xs font-black text-brand uppercase tracking-wider" x-text="activeMaterial?.subject"></span>
                                <span class="text-ink-mute">·</span>
                                <span class="text-xs font-bold text-navy bg-white px-2.5 py-0.5 rounded-full border border-sun-soft" x-text="'Kelas ' + (activeMaterial?.grade_level || '')"></span>
                            </div>
                            <h2 id="material-modal-title" class="text-lg sm:text-xl font-extrabold text-navy leading-snug" x-text="activeMaterial?.title"></h2>
                            <p class="text-xs text-ink-soft font-semibold mt-1" x-text="'Disusun oleh: ' + (activeMaterial?.author || 'Guru Pengampu')"></p>
                        </div>
                        <button
                            type="button"
                            @click="activeMaterial = null"
                            aria-label="Tutup modal materi"
                            class="p-2 rounded-xl text-ink-mute hover:text-navy hover:bg-white transition-colors min-h-[44px] min-w-[44px] flex items-center justify-center"
                        >
                            <x-app-icon name="x" class="w-5 h-5" />
                        </button>
                    </div>

                    <div class="p-5 sm:p-6 overflow-y-auto space-y-4 grow">
                        <template x-if="activeMaterial?.summary">
                            <div class="p-3.5 rounded-xl bg-cream/60 border border-sun-soft text-xs sm:text-sm text-navy font-semibold leading-relaxed" x-text="activeMaterial?.summary"></div>
                        </template>

                        <div class="prose max-w-none text-navy text-sm sm:text-base leading-relaxed whitespace-pre-line font-medium" x-text="activeMaterial?.content"></div>
                    </div>

                    <div class="p-4 bg-cream/60 border-t-2 border-sun-soft flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <template x-if="activeMaterial?.attachment_url">
                                <a
                                    :href="activeMaterial?.attachment_url"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="btn-primary text-xs px-5 shadow-2xs"
                                >
                                    <x-app-icon name="download" class="w-4 h-4" />
                                    <span>Unduh Lampiran Modul</span>
                                </a>
                            </template>
                        </div>
                        <button
                            type="button"
                            @click="activeMaterial = null"
                            class="btn-ghost text-xs"
                        >
                            Tutup Bacaan
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>
</x-layouts.app>
