<?php

use Database\Seeders\StudentUserSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (! Schema::hasColumn('users', 'nis')) {
                    $table->string('nis', 20)->nullable()->index()->after('nisn');
                }
                if (! Schema::hasColumn('users', 'gender')) {
                    $table->string('gender', 5)->nullable()->index()->after('nis');
                }
                if (! Schema::hasColumn('users', 'birth_place')) {
                    $table->string('birth_place', 100)->nullable()->after('gender');
                }
                if (! Schema::hasColumn('users', 'birth_date')) {
                    $table->date('birth_date')->nullable()->after('birth_place');
                }
            });
        }

        if (Schema::hasTable('users') && Schema::hasTable('roles')) {
            (new StudentUserSeeder())->run();
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $cols = [];
                foreach (['nis', 'gender', 'birth_place', 'birth_date'] as $col) {
                    if (Schema::hasColumn('users', $col)) {
                        $cols[] = $col;
                    }
                }
                if (! empty($cols)) {
                    $table->dropColumn($cols);
                }
            });
        }
    }
};
