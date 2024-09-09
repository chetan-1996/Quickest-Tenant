<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Stancl\Tenancy\Exceptions\TenantCouldNotBeIdentifiedException;
use Stancl\Tenancy\Resolvers\DomainTenantResolver;
use Stancl\Tenancy\Resolvers\SubdomainTenantResolver;
use Stancl\Tenancy\Tenancy;

class InitializeTenancyManually
{
    protected $tenancy;
    protected $domainTenantResolver;
    protected $subdomainTenantResolver;

    public function __construct(Tenancy $tenancy, DomainTenantResolver $domainTenantResolver, SubdomainTenantResolver $subdomainTenantResolver)
    {
        $this->tenancy = $tenancy;
        $this->domainTenantResolver = $domainTenantResolver;
        $this->subdomainTenantResolver = $subdomainTenantResolver;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            // Try to resolve tenant by domain first
            $tenant = $this->domainTenantResolver->resolve($request);

            // If not found by domain, try to resolve by subdomain
            if (!$tenant) {
                $tenant = $this->subdomainTenantResolver->resolve($request);
            }

            // Initialize the tenant
            if ($tenant) {
                $this->tenancy->initialize($tenant);
            } else {
                throw new TenantCouldNotBeIdentifiedException;
            }
        } catch (TenantCouldNotBeIdentifiedException $e) {
            // Handle tenant identification failure
            abort(404, 'Tenant could not be identified');
        }

        return $next($request);
    }
}
