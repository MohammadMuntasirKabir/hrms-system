<?php

namespace App\View\Composers;

use App\Models\Company;
use Illuminate\View\View;

class SidebarComposer
{
    /**
     * Bind data to the sidebar layout.
     */
    public function compose(View $view): void
    {
        $user = auth()->user();
        $companies = collect();

        if ($user && $user->isSuperAdmin()) {
            $companies = Company::where('is_active', true)->orderBy('name')->get();
        }

        $view->with('sidebarCompanies', $companies);
    }
}
