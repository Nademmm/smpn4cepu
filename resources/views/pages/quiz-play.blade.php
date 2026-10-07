<x-layouts.app :title="$bank->title . ' - Latihan Mandiri'">
    <x-page-header
        :title="$bank->title"
        :subtitle="($bank->subject?->name ?? 'Mata Pelajaran') . ' · Kelas ' . $bank->grade_level . ' · Durasi: ' . $bank->duration_minutes . ' Menit · KKM: ' . ($bank->passing_score ?? 75)"
        crumb="Simulasi Latihan Soal"
        variant="violet"
    />

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <livewire:quiz.quiz-room :bankId="$bank->id" />
    </div>
</x-layouts.app>
