<?php

namespace App\Providers;

use App\Models\Company;
use App\Models\Department;
use App\Observers\CompanyObserver;
use App\Observers\DepartmentObserver;
use App\View\Composers\SidebarComposer;
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
        //
    }

    public function boot(): void
    {
        URL::forceScheme('https');
        $this->configureDefaults();
        $this->configureViewComposers();
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

    protected function configureViewComposers(): void
    {
        // View composers registered here if needed
    }

    protected function configureObservers(): void
    {
        Company::observe(CompanyObserver::class);
        Department::observe(DepartmentObserver::class);
    }
}
