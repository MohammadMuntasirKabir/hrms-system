<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StoreCompanyFilter
{
    /**
     * Store the company filter in the session for persistence across navigation.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->has('company_id')) {
            $companyId = $request->input('company_id');
            if ($companyId === '') {
                session()->forget('filter_company_id');
            } else {
                session(['filter_company_id' => (int) $companyId]);
            }
        }

        return $next($request);
    }
}
