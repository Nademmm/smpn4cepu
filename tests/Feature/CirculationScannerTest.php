<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\BookReservation;
use App\Models\User;
use App\Services\BarcodeService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CirculationScannerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_book_reservations_page_uses_photo_scanner_with_separate_pinjam_and_kembali_buttons(): void
    {
        /** @var User $librarian */
        $librarian = User::factory()->create();
        $librarian->assignRole('staf_perpus');

        $response = $this->actingAs($librarian)->get('/admin/book-reservations');
        $response->assertStatus(200);
        $response->assertSee('Scan Pinjam Buku', false);
        $response->assertSee('Scan Kembali Buku', false);
        $response->assertSee('circulationScannerComponent', false);
        $response->assertSee('decodeQrFromImageSource', false);
        $response->assertDontSee('acquireCameraStream', false);
        $response->assertDontSee('getUserMedia', false);
        $response->assertDontSee('decodeQrFromVideoFrame', false);

        $pinjamModalHtml = view('filament.components.circulation-camera-modal', [
            'actionMode' => 'hand_over',
        ])->render();
        $this->assertStringContainsString("circulationScanner('hand_over')", $pinjamModalHtml);
        $this->assertStringContainsString('Mode Peminjaman', $pinjamModalHtml);
        $this->assertStringContainsString('capture="environment"', $pinjamModalHtml);
        $this->assertStringContainsString('clearPreview()', $pinjamModalHtml);
        $this->assertStringNotContainsString('Video Live Scanner', $pinjamModalHtml);
        $this->assertStringNotContainsString('liveVideoEl', $pinjamModalHtml);
        $this->assertStringNotContainsString('circulation-action-mode', $pinjamModalHtml);

        $kembaliModalHtml = view('filament.components.circulation-camera-modal', [
            'actionMode' => 'return',
        ])->render();
        $this->assertStringContainsString("circulationScanner('return')", $kembaliModalHtml);
        $this->assertStringContainsString('Mode Pengembalian', $kembaliModalHtml);
        $this->assertStringContainsString('Proses Pengembalian', $kembaliModalHtml);
        $this->assertStringNotContainsString('circulation-action-mode', $kembaliModalHtml);
    }

    public function test_qr_svg_has_clean_quiet_zone_and_process_scan_endpoint_works_for_hand_over_and_return(): void
    {
        /** @var User $librarian */
        $librarian = User::factory()->create();
        $librarian->assignRole('staf_perpus');

        /** @var User $student */
        $student = User::factory()->create([
            'student_class' => '8A',
            'nisn' => '99' . str_pad((string) random_int(10000000, 99999999), 8, '0', STR_PAD_LEFT),
            'status' => 'aktif',
        ]);

        $book = Book::create([
            'title' => 'IPA Terpadu Kelas VIII Uji Scanner',
            'slug' => 'ipa-terpadu-uji-scanner-' . uniqid(),
            'category' => \App\Enums\BookCategory::KELAS_8->value,
            'author' => 'Tim Penulis',
            'physical_stock' => 2,
            'is_active' => true,
        ]);

        $suffix = strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
        $ticketCode = 'LIB-2026-' . $suffix;

        $reservation = BookReservation::create([
            'book_id' => $book->id,
            'user_id' => $student->id,
            'reservation_code' => $ticketCode,
            'borrower_name' => $student->name,
            'borrower_class' => '8A',
            'borrower_nisn' => $student->nisn,
            'status' => BookReservation::STATUS_PENDING,
            'reserved_at' => now(),
            'pickup_date' => now()->addDay(),
        ]);

        $svg = BarcodeService::generateQrCodeSvg($reservation->reservation_code);
        $this->assertStringContainsString('<svg', $svg);

        // 1. Return mode on PENDING ticket should reject gracefully with ticket info
        $wrongReturn = $this->actingAs($librarian)->postJson(route('admin.circulation.process-scan'), [
            'code' => $reservation->reservation_code,
            'circulation_action' => 'return',
        ]);
        $wrongReturn->assertStatus(422)
            ->assertJson([
                'success' => false,
                'ticket' => [
                    'code' => $ticketCode,
                    'status' => BookReservation::STATUS_PENDING,
                ],
            ]);

        // 2. Hand-over mode on PENDING ticket hands over the book (checkout)
        $handOverResponse = $this->actingAs($librarian)->postJson(route('admin.circulation.process-scan'), [
            'code' => $reservation->reservation_code,
            'circulation_action' => 'hand_over',
        ]);

        $handOverResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'action_done' => 'diserahkan',
                'ticket' => [
                    'code' => $ticketCode,
                    'status' => BookReservation::STATUS_BORROWED,
                ],
            ]);

        // 3. Hand-over mode on already BORROWED ticket rejects with ticket info
        $wrongHandOver = $this->actingAs($librarian)->postJson(route('admin.circulation.process-scan'), [
            'code' => $suffix,
            'circulation_action' => 'hand_over',
        ]);
        $wrongHandOver->assertStatus(422)
            ->assertJson([
                'success' => false,
                'ticket' => [
                    'code' => $ticketCode,
                    'status' => BookReservation::STATUS_BORROWED,
                ],
            ]);

        // 4. Return mode on BORROWED ticket receives return
        $returnResponse = $this->actingAs($librarian)->postJson(route('admin.circulation.process-scan'), [
            'code' => $suffix,
            'circulation_action' => 'return',
        ]);

        $returnResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'action_done' => 'dikembalikan',
                'ticket' => [
                    'code' => $ticketCode,
                    'status' => BookReservation::STATUS_RETURNED,
                ],
            ]);
    }
}
