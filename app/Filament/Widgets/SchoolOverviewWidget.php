<?php

namespace App\Filament\Widgets;

use App\Models\ElectionVote;
use App\Models\LibraryBook;
use App\Models\StaffMember;
use App\Models\StudentStatistic;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SchoolOverviewWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalStudents = StudentStatistic::sum('total_count');
        $totalStaff = StaffMember::count();
        $totalBooks = LibraryBook::sum('available_stock');
        $totalVotes = ElectionVote::count();

        return [
            Stat::make('Total Siswa Terdata', number_format($totalStudents))
                ->description('Tahun Ajaran 2025/2026')
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
