<?php

namespace App\Http\Middleware;

use App\Support\ArchiveAccess;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRegistryAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! ArchiveAccess::canManageRegistry()) {
            abort(404);
        }

        return $next($request);
    }
}
