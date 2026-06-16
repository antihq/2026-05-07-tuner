<?php

namespace App\Http\Middleware;

use App\Services\ErrorTracker;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FlushErrorTracker
{
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        app(ErrorTracker::class)->flush();
    }
}
