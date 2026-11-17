<?php

namespace App\Filament\Resources\QuizAttempts\Pages;

use App\Filament\Resources\QuizAttempts\QuizAttemptResource;
use App\Services\Support\FlexibleDocumentReader;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListQuizAttempts extends ListRecords
{
    protected static string $resource = QuizAttemptResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportScoresXlsx')
                ->label('Export Rekap Nilai (.xlsx)')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(function (): StreamedResponse {
                    $attempts = QuizAttemptResource::getEloquentQuery()
                        ->with(['user', 'academicYear', 'bank.subject'])
                        ->orderByDesc('completed_at')
                        ->get();

                    $rows = [[
                        'No',
                        'Nama Peserta Didik',
                        'NISN',
                        'Rombel Saat Kuis',
                        'Tahun Ajaran',
                        'Mata Pelajaran',
                        'Paket Soal / Ujian',
                        'Jawaban Benar',
                        'Total Soal',
                        'Nilai Akhir',
                        'Status KKM',
                        'Waktu Selesai',
                    ]];

                    foreach ($attempts as $idx => $attempt) {
                        $rows[] = [
                            (string) ($idx + 1),
                            (string) ($attempt->user?->name ?: $attempt->participant_identifier),
                            (string) ($attempt->user?->nisn ?? '-'),
                            (string) ($attempt->student_class ?: ($attempt->user?->student_class ?? '-')),
                            (string) ($attempt->academicYear?->name ?? '2026/2027'),
                            (string) ($attempt->bank?->subject?->name ?? '-'),
                            (string) ($attempt->bank?->title ?? '-'),
                            (string) ($attempt->correct_answers ?? 0),
                            (string) ($attempt->total_questions ?? 0),
                            number_format((float) $attempt->final_score, 2, '.', ''),
                            $attempt->is_passed ? 'Lulus KKM' : 'Remedial',
                            $attempt->completed_at ? $attempt->completed_at->format('d/m/Y H:i') : '-',
                        ];
                    }

                    $xlsx = FlexibleDocumentReader::buildXlsx([
                        ['name' => 'Rekap Nilai Kuis', 'rows' => $rows],
                    ]);

                    return response()->streamDownload(function () use ($xlsx): void {
                        echo $xlsx;
                    }, 'rekap_nilai_kuis_smpn4cepu_' . date('Ymd_His') . '.xlsx', [
                        'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    ]);
                }),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua Hasil Kuis')
                ->badge(fn (): int => QuizAttemptResource::getEloquentQuery()->count()),

            'passed' => Tab::make('Tuntas (Lulus KKM)')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('is_passed', true))
                ->badge(fn (): int => (clone QuizAttemptResource::getEloquentQuery())->where('is_passed', true)->count())
                ->badgeColor('success'),

            'remedial' => Tab::make('Belum Tuntas (Remedial)')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('is_passed', false))
                ->badge(fn (): int => (clone QuizAttemptResource::getEloquentQuery())->where('is_passed', false)->count())
                ->badgeColor('danger'),
        ];
    }
}
