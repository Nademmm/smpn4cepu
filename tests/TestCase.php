<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Schema;

abstract class TestCase extends BaseTestCase
{
    use DatabaseTransactions;

    protected static bool $bootstrappedDatabase = false;

    protected function setUp(): void
    {
        parent::setUp();

        if (! static::$bootstrappedDatabase) {
            if (! Schema::hasTable('users') || User::count() === 0) {
                $this->artisan('migrate', ['--force' => true]);
                $this->artisan('db:seed', ['--force' => true]);
            }
            static::$bootstrappedDatabase = true;
        }
    }
}
