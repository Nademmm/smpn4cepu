<x-layouts.app :title="$bank->title . ' - Latihan Mandiri'">
    <section class="bg-gradient-to-b from-blue-900 via-indigo-950 to-slate-950 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <a href="{{ route('quizzes') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-blue-300 hover:text-white mb-3">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Kembali ke Daftar Latihan</span>
            </a>
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-blue-400">{{ $bank->subject->name ?? 'Mata Pelajaran' }} (Kelas {{ $bank->grade_level }})</span>
                    <h1 class="text-2xl sm:text-3xl font-black mt-1">{{ $bank->title }}</h1>
                </div>
                <div class="flex items-center gap-3 bg-white/10 px-4 py-2 rounded-xl backdrop-blur-sm text-xs font-bold">
                    <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Durasi: {{ $bank->duration_minutes }} Menit</span>
                </div>
            </div>
        </div>
    </section>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <livewire:quiz.quiz-room :bankId="$bank->id" />
    </div>
</x-layouts.app>
