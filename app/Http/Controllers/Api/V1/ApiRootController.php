<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class ApiRootController extends Controller
{
    /**
     * GET / — API entry point that links to the top-level resources (HATEOAS).
     * Everything except the auth endpoints requires a Bearer access token.
     */
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'name' => 'FinPlan API',
            'version' => 'v1',
            '_links' => [
                'self' => ['href' => route('api.root'), 'method' => 'GET'],
                'register' => ['href' => route('auth.register'), 'method' => 'POST'],
                'login' => ['href' => route('auth.login'), 'method' => 'POST'],
                'refresh' => ['href' => route('auth.refresh'), 'method' => 'POST'],
                'me' => ['href' => route('auth.me'), 'method' => 'GET'],
                'categories' => ['href' => route('categories.index'), 'method' => 'GET'],
                'create_category' => ['href' => route('categories.store'), 'method' => 'POST'],
                'dashboard' => ['href' => route('dashboard'), 'method' => 'GET'],
                'users' => ['href' => route('users.index'), 'method' => 'GET'],
                'documentation' => ['href' => url('/docs'), 'method' => 'GET'],
            ],
        ]);
    }
}
