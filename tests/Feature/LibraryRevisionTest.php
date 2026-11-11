<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\BookReservation;
use App\Models\User;
use App\Services\BarcodeService;
use App\Services\BookImportService;
use Livewire\Livewire;
use Tests\TestCase;

class LibraryRevisionTest extends TestCase
{
    /**
     * Test 1: Import Massal Buku via BookImportService (CSV & Excel)
     */
    public function test_bulk_book_import_via_csv(): void
    {
        $csvContent = "\xEF\xBB\xBF" .
            "Judul Buku;Nomor ISBN;Penulis / Pengarang;Penerbit;Tahun Terbit;Kategori;Stok Fisik;Deskripsi\n" .
            "Fisika Dasar SMP Kelas VIII;9786022449900;Dr. Bambang S.;Pusat Perbukuan;2024;kelas-8;25;Materi gerak, gaya, dan energi mekanik.\n" .
            "Biologi Lingkungan Hidup;9786022449911;Dr. Siti M.;Pusat Perbukuan;2024;kelas-9;30;Pembahasan ekosistem dan keanekaragaman hayati.";

        $tempPath = tempnam(sys_get_temp_dir(), 'test_books_') . '.csv';
        file_put_contents($tempPath, $csvContent);

        $service = new BookImportService();
        $result = $service->import($tempPath);

        @unlink($tempPath);

        $this->assertTrue($result['success']);
        $this->assertGreaterThanOrEqual(2, $result['imported'] + $result['updated']);

        $book1 = Book::where('isbn', '9786022449900')->first();
        $this->assertNotNull($book1);
        $this->assertEquals('Fisika Dasar SMP Kelas VIII', $book1->title);
        $this->assertEquals(25, $book1->physical_stock);
        $this->assertEquals('kelas_8', $book1->category);
        $this->assertNotNull($book1->cover_path);
    }

    /**
     * Test 2: Template CSV Buku dapat dibangkitkan dengan UTF-8 BOM
     */
    public function test_template_csv_contains_required_headers(): void
    {
        $template = BookImportService::getTemplateCsv();

        $this->assertStringContainsString('Judul Buku', $template);
        $this->assertStringContainsString('Nomor ISBN', $template);
        $this->assertStringContainsString('Stok Fisik', $template);
        $this->assertStringContainsString('kelas-7', $template);
    }

    /**
     * Test 3: Siswa dapat mengajukan peminjaman buku fisik dan memperoleh tiket ber-Barcode
     */
    public function test_physical_book_borrowing_generates_barcode_ticket(): void
    {
        $book = Book::firstOrCreate(
            ['isbn' => '9786029998877'],
            [
                'title' => 'Buku Uji Coba Peminjaman Fisik',
                'slug' => 'buku-uji-coba-peminjaman-fisik',
                'author' => 'Tim Penguji',
                'category' => 'literasi-umum',
                'physical_stock' => 5,
                'is_active' => true,
            ]
        );

        $initialStock = $book->physical_stock;

        $response = $this->post(route('library.reserve'), [
            'book_id' => $book->id,
            'borrower_name' => 'Ahmad Fajar',
            'borrower_class' => '8A',
            'pickup_date' => now()->format('Y-m-d'),
        ]);

        $response->assertStatus(200);
        $json = $response->json();

        $this->assertTrue($json['success']);
        $this->assertStringStartsWith('LIB-' . date('Y'), $json['reservation_code']);
        $this->assertStringContainsString('<svg', $json['barcode_svg']);

        $reservation = BookReservation::where('reservation_code', $json['reservation_code'])->first();
        $this->assertNotNull($reservation);
        $this->assertEquals('menunggu', $reservation->status);
        $this->assertNotNull($reservation->due_date);

        // Verifikasi Barcode SVG & QR SVG pada Model
        $this->assertStringContainsString('<svg', $reservation->barcode_svg);
        $this->assertStringContainsString('<rect', $reservation->barcode_svg);
        $this->assertStringContainsString($reservation->reservation_code, $reservation->barcode_svg);
        $this->assertStringContainsString('<svg', $reservation->qr_svg);

        // Verifikasi alur stok fisik: eksemplar langsung dialokasikan saat reservasi dibuat
        $book->refresh();
        $this->assertEquals($initialStock - 1, $book->physical_stock);

        $loanService = app(\App\Services\LibraryLoanService::class);

        // Uji coba alur serah terima buku fisik di meja sirkulasi: status beralih ke 'diambil'
        $loanService->handOver($reservation);
        $reservation->refresh();
        $this->assertEquals('diambil', $reservation->status);
        $this->assertNotNull($reservation->borrowed_at);

        // Halaman bukti tiket siap cetak dapat diakses secara publik
        $this->get(route('library.ticket', $reservation->reservation_code))
            ->assertStatus(200)
            ->assertSee($reservation->reservation_code)
            ->assertSee($book->title);

        // Uji coba pengembalian buku: status beralih ke 'selesai' dan stok fisik kembali ke katalog
        $loanService->receiveReturn($reservation);
        $reservation->refresh();
        $this->assertEquals('selesai', $reservation->status);
        $this->assertNotNull($reservation->returned_at);

        $book->refresh();
        $this->assertEquals($initialStock, $book->physical_stock);
    }

    /**
     * Test 4: Komponen Livewire DigitalLibrary menangani alur peminjaman & tiket
     */
    public function test_digital_library_livewire_borrow_and_ticket_modal(): void
    {
        $book = Book::where('physical_stock', '>', 0)->first();
        $this->assertNotNull($book);

        $component = Livewire::test(\App\Livewire\Public\DigitalLibrary::class)
            ->call('openBorrowModal', $book->id)
            ->assertSet('showBorrowModal', true)
            ->assertSet('borrowBookId', $book->id)
            ->set('borrowerName', 'Siti Rahma')
            ->set('borrowerClass', '9B')
            ->set('pickupDate', now()->format('Y-m-d'))
            ->call('submitBorrow')
            ->assertHasNoErrors()
            ->assertSet('showBorrowModal', false)
            ->assertSet('showTicketModal', true);

        $issuedReservation = $component->get('activeReservation');
        $this->assertNotNull($issuedReservation);

        // Uji coba pelacakan tiket mandiri oleh siswa di komponen publik
        $component->call('openCheckTicketModal')
            ->assertSet('showCheckTicketModal', true)
            ->set('ticketSearchCode', $issuedReservation->reservation_code)
            ->call('searchTicket')
            ->assertHasNoErrors();

        $this->assertNotNull($component->get('checkedTicket'));
        $this->assertEquals($issuedReservation->reservation_code, $component->get('checkedTicket')->reservation_code);
    }

    /**
     * Test 5: Restrukturisasi Hak Akses (Guru adalah Viewer buku & berita, tidak bisa Create/Edit)
     */
    public function test_teacher_is_viewer_for_library_and_news(): void
    {
        $guru = User::where('email', 'guru@smpn4cepu.sch.id')->first();
        $this->assertNotNull($guru);

        // 1. Guru bisa melihat katalog buku di Filament (Viewer)
        $this->actingAs($guru)->get('/admin/books')->assertStatus(200);

        // 2. Guru TIDAK BISA mengakses form tambah buku fisik (403 Forbidden)
        $this->actingAs($guru)->get('/admin/books/create')->assertStatus(403);

        // 3. Guru TIDAK BISA mengakses form tambah berita / Post (403 Forbidden)
        $this->actingAs($guru)->get('/admin/posts/create')->assertStatus(403);

        // 4. Guru TETAP BISA mengakses Materi Pelajaran & Bank Soal mereka sendiri
        $this->actingAs($guru)->get('/admin/learning-materials')->assertStatus(200);
        $this->actingAs($guru)->get('/admin/learning-materials/create')->assertStatus(200);
        $this->actingAs($guru)->get('/admin/question-banks')->assertStatus(200);
        $this->actingAs($guru)->get('/admin/question-banks/create')->assertStatus(200);

        // 5. Staf Perpustakaan BISA mengakses form tambah buku
        $perpus = User::where('email', 'perpus@smpn4cepu.sch.id')->first();
        $this->assertNotNull($perpus);
        $this->flushSession();
        $this->actingAs($perpus)->get('/admin/books')->assertStatus(200);
        $this->actingAs($perpus)->get('/admin/books/create')->assertStatus(200);
        $this->actingAs($perpus)->get('/admin/book-reservations')->assertStatus(200);

        // 6. Super Admin BISA membuat berita dan buku
        $admin = User::where('email', 'admin@smpn4cepu.sch.id')->first();
        $this->assertNotNull($admin);
        $this->flushSession();
        $this->actingAs($admin)->get('/admin/posts/create')->assertStatus(200);
        $this->actingAs($admin)->get('/admin/books/create')->assertStatus(200);
    }

    /**
     * Test 6: Histori peminjaman buku tercatat per akun siswa dan dapat diakses
     */
    public function test_student_account_tracks_borrowing_history(): void
    {
        $student = User::whereHas('roles', fn ($q) => $q->where('name', 'siswa'))->first();
        if (! $student) {
            $student = User::create([
                'name' => 'Budi Santoso',
                'email' => 'budi.siswa@smpn4cepu.sch.id',
                'password' => bcrypt('password'),
                'nisn' => '0087654321',
                'student_class' => '8B',
            ]);
            $student->assignRole('siswa');
        }

        $book = Book::where('physical_stock', '>', 0)->first();
        $this->assertNotNull($book);

        // Siswa meminjam buku via Livewire dalam kondisi masuk akun
        Livewire::actingAs($student)
            ->test(\App\Livewire\Public\DigitalLibrary::class)
            ->call('openBorrowModal', $book->id)
            ->assertSet('borrowerName', $student->name)
            ->assertSet('borrowerNisn', $student->nisn)
            ->set('pickupDate', now()->format('Y-m-d'))
            ->call('submitBorrow')
            ->assertHasNoErrors();

        // Verifikasi bahwa user_id tercatat pada BookReservation
        $reservation = BookReservation::where('user_id', $student->id)->latest()->first();
        $this->assertNotNull($reservation);
        $this->assertEquals($book->id, $reservation->book_id);
        $this->assertEquals($student->id, $reservation->user->id);

        // Verifikasi relasi User->bookReservations
        $this->assertTrue($student->bookReservations()->where('book_id', $book->id)->exists());

        // Verifikasi halaman riwayat peminjaman siswa dapat diakses
        $this->actingAs($student)
            ->get(route('library.history'))
            ->assertStatus(200)
            ->assertSee($reservation->reservation_code)
            ->assertSee($book->title);

        // Verifikasi pelacakan dengan NISN pada modal publik
        Livewire::test(\App\Livewire\Public\DigitalLibrary::class)
            ->call('openCheckTicketModal')
            ->set('ticketSearchCode', $student->nisn)
            ->call('searchTicket')
            ->assertHasNoErrors()
            ->assertSet('checkTicketErrorMessage', null);
    }

    /**
     * Test 7: Endpoint Pemindaian Barcode Kamera Sirkulasi (/admin/circulation/process-scan)
     */
    public function test_camera_scan_endpoint_processes_circulation_automatically(): void
    {
        $perpus = User::where('email', 'perpus@smpn4cepu.sch.id')->first();
        $this->assertNotNull($perpus);

        $book = Book::where('physical_stock', '>', 0)->first();
        $this->assertNotNull($book);
        $initialStock = $book->physical_stock;

        $loanService = app(\App\Services\LibraryLoanService::class);
        $ticket = $loanService->reserve(
            bookId: $book->id,
            name: 'Ahmad Siswa Kamera',
            class: '7A',
            nisn: '1234567890',
            pickupDate: now()->format('Y-m-d')
        );

        $this->assertEquals(BookReservation::STATUS_PENDING, $ticket->status);

        // 1. Scan Pertama: Penyerahan Buku ke Siswa (Status berubah jadi 'diambil')
        $response1 = $this->actingAs($perpus)->postJson(route('admin.circulation.process-scan'), [
            'code' => $ticket->reservation_code,
            'circulation_action' => 'auto',
        ]);

        $response1->assertStatus(200);
        $response1->assertJsonPath('success', true);
        $response1->assertJsonPath('action_done', 'diserahkan');
        $response1->assertJsonPath('ticket.status', BookReservation::STATUS_BORROWED);

        // 2. Scan Kedua: Pengembalian Buku oleh Siswa (Status berubah jadi 'selesai')
        $response2 = $this->actingAs($perpus)->postJson(route('admin.circulation.process-scan'), [
            'code' => $ticket->reservation_code,
            'circulation_action' => 'auto',
        ]);

        $response2->assertStatus(200);
        $response2->assertJsonPath('success', true);
        $response2->assertJsonPath('action_done', 'dikembalikan');
        $response2->assertJsonPath('ticket.status', BookReservation::STATUS_RETURNED);

        // Stok fisik buku harus kembali seperti semula
        $book->refresh();
        $this->assertEquals($initialStock, $book->physical_stock);

        // 3. Verifikasi QR Code dan Barcode SVG tergenerate
        $qrSvg = BarcodeService::generateQrCodeSvg($ticket->reservation_code);
        $this->assertStringContainsString('<svg', $qrSvg);
        $this->assertStringContainsString('</svg>', $qrSvg);

        $barcodeSvg = BarcodeService::generateCode128Svg($ticket->reservation_code);
        $this->assertStringContainsString('<svg', $barcodeSvg);
        $this->assertStringContainsString('</svg>', $barcodeSvg);
    }
}
