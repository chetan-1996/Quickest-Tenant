<?php

namespace App\Resolvers;

use Stancl\Tenancy\Contracts\Tenant;
use Stancl\Tenancy\Contracts\TenantResolver as TenantResolverContract;
use Stancl\Tenancy\Database\Models\Tenant as StanclTenant;

class CustomTenantResolver implements TenantResolverContract
{
    public function resolve($identifier): ?Tenant
    {
        
        // Change 'slug' to any other column you want to use for identification
        return StanclTenant::where('domain', $identifier)->first();
    }
}
