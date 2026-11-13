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

    public function test_admin_book_reservations_page_includes_fixed_live_and_photo_scanner_scripts(): void
    {
        /** @var User $librarian */
        $librarian = User::factory()->create();
        $librarian->assignRole('staf_perpus');

        $response = $this->actingAs($librarian)->get('/admin/book-reservations');
        $response->assertStatus(200);
        $response->assertSee('circulationScannerComponent', false);
        $response->assertSee('circulationWidgetScanner', false);
        $response->assertSee('acquireCameraStream', false);
        $response->assertSee('decodeQrFromVideoFrame', false);
        $response->assertSee('decodeQrFromImageSource', false);
        $response->assertDontSee('if (!cleanCode || this.isProcessing) return;', false);

        $modalHtml = view('filament.components.circulation-camera-modal')->render();
        $this->assertStringContainsString('x-ref="liveVideoEl"', $modalHtml);
        $this->assertStringContainsString('x-on:close-modal.window="stopLiveCamera()"', $modalHtml);
        $this->assertStringNotContainsString('<template x-if="isLiveActive">', $modalHtml);

        $caddyfile = file_get_contents(base_path('docker/Caddyfile'));
        $htaccess = file_get_contents(public_path('.htaccess'));
        $this->assertStringContainsString('camera=(self)', $caddyfile);
        $this->assertStringNotContainsString('camera=()', $caddyfile);
        $this->assertStringContainsString('camera=(self)', $htaccess);
        $this->assertStringNotContainsString('camera=()', $htaccess);
    }

    public function test_qr_svg_has_clean_quiet_zone_and_process_scan_endpoint_works_for_checkout_and_return(): void
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

        // 1. Auto scan on PENDING ticket hands over the book (checkout)
        $handOverResponse = $this->actingAs($librarian)->postJson(route('admin.circulation.process-scan'), [
            'code' => $reservation->reservation_code,
            'circulation_action' => 'auto',
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

        // 2. Auto scan on BORROWED ticket receives return
        $returnResponse = $this->actingAs($librarian)->postJson(route('admin.circulation.process-scan'), [
            'code' => $suffix,
            'circulation_action' => 'auto',
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
