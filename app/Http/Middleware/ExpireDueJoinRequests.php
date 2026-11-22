<?php

namespace App\Http\Middleware;

use App\Support\JoinRequestExpiry;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ExpireDueJoinRequests
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        JoinRequestExpiry::markDue();

        return $next($request);
    }
}
