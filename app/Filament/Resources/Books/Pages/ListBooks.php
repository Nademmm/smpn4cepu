<?php

namespace App\Filament\Resources\Books\Pages;

use App\Filament\Resources\Books\BookResource;
use App\Models\Book;
use App\Services\BookImportService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListBooks extends ListRecords
{
    protected static string $resource = BookResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // 1. Tombol Unduh Template Excel / CSV
            Action::make('downloadTemplate')
                ->label('Unduh Template Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(function (): StreamedResponse {
                    $csvContent = BookImportService::getTemplateCsv();
                    return response()->streamDownload(function () use ($csvContent) {
                        echo $csvContent;
                    }, 'template_input_buku_smpn4cepu.csv', [
                        'Content-Type' => 'text/csv; charset=UTF-8',
                    ]);
                }),

            // 2. Tombol Import Massal Excel / CSV
            Action::make('importExcel')
                ->label('Import Excel / CSV')
                ->icon('heroicon-o-document-arrow-up')
                ->color('success')
                ->modalHeading('Import Data Buku Massal')
                ->modalDescription('Unggah berkas Excel (.xlsx) atau CSV sesuai template resmi. Sistem akan membaca seluruh baris buku dan memasukkannya ke katalog secara otomatis.')
                ->modalSubmitActionLabel('Mulai Import Data')
                ->form([
                    FileUpload::make('attachment')
                        ->label('Berkas Excel / CSV')
                        ->disk('local')
                        ->directory('temp_imports')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'text/csv',
                            'text/plain',
                            'application/csv',
                        ])
                        ->required()
                        ->helperText('Format yang didukung: .xlsx atau .csv. Maksimal ukuran berkas 15 MB.'),
                ])
                ->action(function (array $data, BookImportService $importService): void {
                    $relativePath = $data['attachment'] ?? null;
                    if (!$relativePath || !Storage::disk('local')->exists($relativePath)) {
                        Notification::make()
                            ->title('Gagal Membaca Berkas')
                            ->body('Berkas yang diunggah tidak ditemukan pada server.')
                            ->danger()
                            ->send();
                        return;
                    }

                    $fullPath = Storage::disk('local')->path($relativePath);
                    $result = $importService->import($fullPath);

                    // Bersihkan berkas upload sementara
                    Storage::disk('local')->delete($relativePath);

                    if ($result['success']) {
                        Notification::make()
                            ->title('Import Buku Berhasil!')
                            ->body("Berhasil menambahkan {$result['imported']} buku baru dan memperbarui {$result['updated']} buku.")
                            ->success()
                            ->duration(6000)
                            ->send();
                    } else {
                        $errorMsg = !empty($result['errors']) ? implode(', ', array_slice($result['errors'], 0, 3)) : 'Periksa kembali format kolom berkas.';
                        Notification::make()
                            ->title('Gagal Melakukan Import')
                            ->body($errorMsg)
                            ->danger()
                            ->send();
                    }
                }),

            // 3. Tombol Ekspor Seluruh Koleksi Buku
            Action::make('exportBooks')
                ->label('Export Katalog')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('gray')
                ->action(function (): StreamedResponse {
                    return response()->streamDownload(function () {
                        $handle = fopen('php://output', 'w');
                        fputs($handle, "\xEF\xBB\xBF"); // UTF-8 BOM
                        fputcsv($handle, [
                            'ID',
                            'Judul Buku',
                            'Nomor ISBN',
                            'Penulis / Pengarang',
                            'Penerbit',
                            'Tahun Terbit',
                            'Kategori',
                            'Stok Fisik',
                            'Jumlah Dilihat',
                            'Tipe Buku',
                            'Status',
                        ], ';');

                        foreach (Book::orderBy('id', 'desc')->cursor() as $b) {
                            fputcsv($handle, [
                                $b->id,
                                $b->title,
                                $b->isbn ?? '-',
                                $b->author ?? '-',
                                $b->publisher ?? '-',
                                $b->published_year ?? '-',
                                $b->category,
                                $b->physical_stock ?? 0,
                                $b->views_count ?? 0,
                                $b->has_digital ? 'E-Book & Fisik' : 'Buku Fisik',
                                $b->is_active ? 'Aktif' : 'Nonaktif',
                            ], ';');
                        }

                        fclose($handle);
                    }, 'katalog_buku_smpn4cepu_' . date('Ymd_His') . '.csv', [
                        'Content-Type' => 'text/csv; charset=UTF-8',
                    ]);
                }),

            // 4. Tombol Buat Manual 1-per-1 bawaan
            CreateAction::make()
                ->label('Tambah Buku (1 per 1)'),
        ];
    }
}
