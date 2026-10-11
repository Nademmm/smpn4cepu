<?php

namespace Tests\Feature;

use App\Filament\GlobalSearch\SmartGlobalSearchProvider;
use App\Models\Post;
use App\Models\StaffMember;
use App\Models\User;
use Filament\Facades\Filament;
use Tests\TestCase;

class GlobalSearchTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_smart_global_search_finds_navigation_pages(): void
    {
        $provider = new SmartGlobalSearchProvider();

        $results = $provider->getResults('guru');
        $this->assertNotNull($results);
        $this->assertTrue($results->getCategories()->has('Menu & Halaman Cepat'));

        $dashboardResults = $provider->getResults('dashboard');
        $this->assertTrue($dashboardResults->getCategories()->has('Menu & Halaman Cepat'));
    }

    public function test_smart_global_search_finds_database_records(): void
    {
        $admin = User::where('email', 'admin@smpn4cepu.sch.id')->first();
        $this->assertNotNull($admin);

        $staff = StaffMember::firstOrCreate(
            ['nip' => '198501012010011001'],
            [
                'name' => 'Budi Santoso S.Pd',
                'position' => 'Guru Matematika',
                'employment_status' => \App\Enums\EmploymentStatus::PNS,
                'is_active' => true,
            ]
        );

        $this->actingAs($admin);

        $provider = new SmartGlobalSearchProvider();

        // Search record 'Budi'
        $staffResults = $provider->getResults('Budi');
        $this->assertNotNull($staffResults);
        $this->assertTrue($staffResults->getCategories()->has('Data Guru & Staf'));
    }
}
