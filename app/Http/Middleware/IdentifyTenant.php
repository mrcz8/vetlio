<?php

namespace App\Http\Middleware;

use App\Models\Organisation;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenant
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request):Response $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $isSubdomain = $this->isSubdomain($request);

        if (!$isSubdomain) {
            abort(404);
        }

        if ($isSubdomain) {
            $tenant = $this->getTenantWithDomain($this->getSubdomain($request));

            if (!$tenant) {
                abort(404);
            }

            $request->merge(['tenant' => $tenant]);

            URL::defaults(['subdomain' => $this->getSubdomain($request)]);;

            return $next($request);
        }

        return $next($request);
    }

    private function isSubdomain(Request $request): bool
    {
        $host = $request->getHost();

        return count(explode('.', $host)) == 3;
    }

    private function getSubdomain(Request $request): string
    {
        $host = $request->getHost();

        return explode('.', $host)[0];
    }

    private function getTenantWithDomain(string $domain): ?Organisation
    {
        return Organisation::query()
            ->whereActive(true)
            ->where('subdomain', $domain)
            ->first();
    }
}
