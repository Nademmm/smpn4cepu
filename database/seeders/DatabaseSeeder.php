<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            StudentUserSeeder::class,
            DemoSchoolSeeder::class,
            AchievementSeeder::class,
            ExtracurricularSeeder::class,
            GalleryPhotoSeeder::class,
            SchoolDocumentSeeder::class,
            SchoolSettingSeeder::class,
        ]);
    }
}
