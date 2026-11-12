<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\Book;
use App\Models\LearningMaterial;
use App\Models\LibraryBook;
use App\Models\Post;
use App\Models\QuestionBank;
use Illuminate\Console\Command;

class PruneTrashCommand extends Command
{
    protected $signature = 'system:prune-trash {--days=30 : Batas hari retensi sebelum dihapus permanen}';

    protected $description = 'Bersihkan data terhapus (Soft Delete) yang melampaui batas hari retensi secara aman';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $cutoff = now()->subDays($days);

        $this->info("Menghapus permanen data di tong sampah yang berumur lebih dari {$days} hari (sebelum {$cutoff->toDateString()})...");

        $prunedMaterials = LearningMaterial::onlyTrashed()->where('deleted_at', '<', $cutoff)->forceDelete();
        $prunedQuestions = QuestionBank::onlyTrashed()->where('deleted_at', '<', $cutoff)->forceDelete();
        $prunedBooks = Book::onlyTrashed()->where('deleted_at', '<', $cutoff)->forceDelete();
        $prunedLibrary = LibraryBook::onlyTrashed()->where('deleted_at', '<', $cutoff)->forceDelete();
        $prunedPosts = Post::onlyTrashed()->where('deleted_at', '<', $cutoff)->forceDelete();

        $totalPruned = $prunedMaterials + $prunedQuestions + $prunedBooks + $prunedLibrary + $prunedPosts;

        $this->info("Berhasil membersihkan {$totalPruned} data terhapus lama secara permanen.");

        if ($totalPruned > 0) {
            ActivityLog::record(
                action: 'prune_trash',
                description: "Sistem otomatis membersihkan {$totalPruned} data terhapus lama yang melampaui retensi {$days} hari.",
                reason: "Pembersihan retensi data permanen terjadwal",
                properties: [
                    'retention_days' => $days,
                    'pruned_materials' => $prunedMaterials,
                    'pruned_questions' => $prunedQuestions,
                    'pruned_books' => $prunedBooks,
                    'pruned_library_books' => $prunedLibrary,
                    'pruned_posts' => $prunedPosts,
                ],
                module: 'Pengaturan'
            );
        }

        return self::SUCCESS;
    }
}

