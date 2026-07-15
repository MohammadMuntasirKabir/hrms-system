<?php

namespace App\Providers;

use App\Database\NeonPgConnector;
use App\Models\Company;
use App\Models\Department;
use App\Observers\CompanyObserver;
use App\Observers\DepartmentObserver;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Use the Neon-aware Postgres connector so the endpoint ID is passed
        // to libpq (required when the client lacks SNI support, e.g. Vercel).
        $this->app->bind('db.connector.pgsql', NeonPgConnector::class);
    }

    public function boot(): void
    {
        URL::forceScheme('https');
        $this->configureDefaults();
        $this->configureObservers();
    }

    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }

    protected function configureObservers(): void
    {
        Company::observe(CompanyObserver::class);
        Department::observe(DepartmentObserver::class);
    }
}
