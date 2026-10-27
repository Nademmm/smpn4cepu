<?php

namespace App\Filament\Widgets;

use App\Models\ElectionVote;
use App\Models\LibraryBook;
use App\Models\StaffMember;
use App\Models\StudentStatistic;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class SchoolOverviewWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $stats = Cache::remember('admin_school_overview_stats', 60, function () {
            $latestAcademicYear = StudentStatistic::max('academic_year');
            $studentQuery = StudentStatistic::query();
            if ($latestAcademicYear) {
                $studentQuery->where('academic_year', $latestAcademicYear);
            }

            return [
                'academicYear' => $latestAcademicYear ?? '2025/2026',
                'totalStudents' => (int) $studentQuery->sum('total_count'),
                'totalStaff' => StaffMember::where('is_active', true)->count(),
                'totalBooks' => (int) LibraryBook::sum('available_stock'),
                'totalVotes' => ElectionVote::count(),
            ];
        });

        $academicYear = $stats['academicYear'] ?? '2025/2026';
        $totalStudents = $stats['totalStudents'];
        $totalStaff = $stats['totalStaff'];
        $totalBooks = $stats['totalBooks'];
        $totalVotes = $stats['totalVotes'];

        return [
            Stat::make('Total Siswa Terdata', number_format($totalStudents))
                ->description('Tahun Ajaran ' . $academicYear)
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('primary'),

            Stat::make('Pendidik & Tenaga Kependidikan', number_format($totalStaff))
                ->description('Status Aktif')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success'),

            Stat::make('Stok Buku Perpustakaan', number_format($totalBooks) . ' Eks.')
                ->description('Siap Dipinjam')
                ->descriptionIcon('heroicon-m-book-open')
                ->color('info'),

            Stat::make('Total Suara Pilketos', number_format($totalVotes))
                ->description('Realtime Ballot Count')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('warning'),
        ];
    }
}
