<?php

namespace Tests\Feature;

use App\Models\ElectionCandidate;
use App\Models\QuestionBank;
use App\Services\PerpusnasBookService;
use App\Services\PilketosFingerprintService;
use App\Services\QuizAssessmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CoreServicesTest extends TestCase
{
    public function test_quiz_assessment_service_zero_client_leakage(): void
    {
        $bank = QuestionBank::where('is_active', true)->first();
        $this->assertNotNull($bank, 'Question bank must exist');

        $service = new QuizAssessmentService();
        $questions = $service->getQuestionsForQuiz($bank->id);

        $this->assertNotEmpty($questions);

        foreach ($questions as $q) {
            foreach ($q['options'] as $option) {
                $this->assertArrayNotHasKey('is_correct', $option);
                $this->assertArrayHasKey('id', $option);
                $this->assertArrayHasKey('text', $option);
            }
        }
    }

    public function test_quiz_assessment_service_scoring_and_review_details(): void
    {
        $bank = QuestionBank::with('questions.options')->where('is_active', true)->first();
        $service = new QuizAssessmentService();

        $answers = [];
        foreach ($bank->questions as $q) {
            $correct = $q->options->firstWhere('is_correct', true);
            $answers[$q->id] = $correct->id;
        }

        $attempt = $service->submitAttempt($bank->id, 'NISN-123456', $answers, now()->subMinutes(10));

        $this->assertEquals(100.00, $attempt->final_score);
        $this->assertTrue($attempt->is_passed);
        $this->assertEquals($bank->questions->count(), $attempt->correct_answers);

        $review = $service->getReviewDetails($attempt->id);
        $this->assertEquals(100.00, $review['score']);
        $this->assertEquals(100.00, $review['final_score']);
        $this->assertEquals($bank->questions->count(), $review['correct_count']);
        $this->assertEquals($bank->questions->count(), $review['total_questions']);
        $this->assertCount($bank->questions->count(), $review['reviews']);
    }

    public function test_pilketos_fingerprint_generation_and_voting(): void
    {
        $service = new PilketosFingerprintService();
        $candidate = ElectionCandidate::first();
        $this->assertNotNull($candidate);

        $initialVotes = $candidate->total_votes_cached;

        $request = Request::create('/pilketos/vote', 'POST', [], [], [], [
            'REMOTE_ADDR' => '192.168.1.45',
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
        ]);

        $hwHash = hash('sha256', uniqid('hardware-id-', true));
        $success = $service->recordVote($request, $candidate->id, $hwHash);

        $this->assertTrue($success);

        $candidate->refresh();
        $this->assertEquals($initialVotes + 1, $candidate->total_votes_cached);

        $this->expectException(\RuntimeException::class);
        $service->recordVote($request, $candidate->id, $hwHash);
    }

    public function test_perpusnas_book_service_parses_four_digit_year_correctly(): void
    {
        Http::fake([
            'openlibrary.org/*' => Http::response([
                'ISBN:9786024246945' => [
                    'title' => 'Laut Bercerita',
                    'authors' => [['name' => 'Leila S. Chudori']],
                    'publishers' => [['name' => 'Kepustakaan Populer Gramedia']],
                    'publish_date' => 'Oct 15, 2017',
                    'number_of_pages' => 379,
                ],
            ], 200),
        ]);

        $service = new PerpusnasBookService();
        $result = $service->lookupByIsbn('978-602-424-694-5');

        $this->assertNotNull($result);
        $this->assertEquals('Laut Bercerita', $result['title']);
        $this->assertEquals(2017, $result['publish_year']);
    }

    public function test_roles_and_permissions_assigned_properly(): void
    {
        $admin = \App\Models\User::where('email', 'admin@smpn4cepu.sch.id')->first();
        $guru = \App\Models\User::where('email', 'guru@smpn4cepu.sch.id')->first();
        $perpus = \App\Models\User::where('email', 'perpus@smpn4cepu.sch.id')->first();
        $pilketos = \App\Models\User::where('email', 'pilketos@smpn4cepu.sch.id')->first();
        $siswa = \App\Models\User::where('nisn', '0081234567')->first();

        $this->assertNotNull($admin);
        $this->assertTrue($admin->hasRole('super_admin'));

        $this->assertNotNull($guru);
        $this->assertTrue($guru->hasRole('guru'));
        $this->assertFalse($guru->can('Delete:Subject'));

        $this->assertNotNull($perpus);
        $this->assertTrue($perpus->hasRole('staf_perpus'));

        $this->assertNotNull($pilketos);
        $this->assertTrue($pilketos->hasRole('panitia_pilketos'));

        $this->assertNotNull($siswa);
        $this->assertTrue($siswa->hasRole('siswa'));
    }
}
