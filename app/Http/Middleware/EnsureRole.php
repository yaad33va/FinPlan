<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Role-based access: "role:admin" lets through only requests whose verified access token
 * has one of the given roles in its "role" claim. Must run after auth:api.
 */
class EnsureRole
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $role = $request->attributes->get('jwt')?->role;

        if (! in_array($role, $roles, true)) {
            throw new AccessDeniedHttpException('This action requires one of the roles: '.implode(', ', $roles).'.');
        }

        return $next($request);
    }
}
