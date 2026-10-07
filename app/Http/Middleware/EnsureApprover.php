<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Demo-grade approval gate. The AI/system has no way to pass this: only a
 * caller carrying the approver role is allowed through.
 *
 * In production this would be backed by real authentication (Sanctum/Passport)
 * plus a role/permission check, not a request header.
 */
class EnsureApprover
{
    public function handle(Request $request, Closure $next): Response
    {
        $role = $request->header('X-Role');

        if ($role !== 'approver') {
            return response()->json([
                'message' => 'Only a human approver can approve quotes.',
            ], 403);
        }

        return $next($request);
    }
}
