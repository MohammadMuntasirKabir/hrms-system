<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Laravel\Fortify\Features;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        // Force a fresh in-memory database for every test. By default
        // RefreshDatabase caches the :memory: PDO connection across tests,
        // so per-test transaction rollbacks don't fully clear leaked rows
        // (e.g. paginated index assertions can see leftovers from earlier
        // tests). Resetting the cached state makes each test start clean.
        RefreshDatabaseState::$migrated = false;
        RefreshDatabaseState::$inMemoryConnections = [];

        parent::setUp();

        // Reset Faker's `unique()` state between tests. The `unique()`
        // modifier keeps process-global state, so over a long suite its
        // pools exhaust and factories start reusing values (e.g. duplicate
        // emails) which violate unique constraints and silently fail to
        // save models. Clearing it each test keeps factories deterministic.
        if (class_exists(App::class)) {
            fake()->unique(true);
        }

        // Flush the session before each test. The StoreCompanyFilter
        // middleware persists `filter_company_id` in the session, and Pest
        // reuses the session across tests in the same process, so a company
        // filter set by one test can leak into the next and change what an
        // index shows.
        if (class_exists(Session::class)) {
            session()->flush();
        }
    }

    protected function skipUnlessFortifyHas(string $feature, ?string $message = null): void
    {
        if (! Features::enabled($feature)) {
            $this->markTestSkipped($message ?? "Fortify feature [{$feature}] is not enabled.");
        }
    }
}
