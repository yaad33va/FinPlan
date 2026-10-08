<?php

use App\Http\Middleware\EnsureRole;
use App\Http\Middleware\ForceJsonResponse;
use App\Http\Middleware\RejectMalformedJson;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [
            ForceJsonResponse::class,
            RejectMalformedJson::class,
        ]);

        $middleware->alias([
            'role' => EnsureRole::class,
        ]);

        // Check the role right after authentication, before route model binding (403 before 404).
        $middleware->appendToPriorityList(AuthenticatesRequests::class, EnsureRole::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (AuthenticationException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            $message = $exception->getMessage() === 'Unauthenticated.'
                ? $request->attributes->get('auth_error', 'Unauthenticated. Send a valid access token in the "Authorization: Bearer <token>" header.')
                : $exception->getMessage();

            return response()->json(['message' => $message], 401, ['WWW-Authenticate' => 'Bearer']);
        });

        $exceptions->render(function (NotFoundHttpException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            $previous = $exception->getPrevious();

            $message = $previous instanceof ModelNotFoundException
                ? sprintf('%s with ID %s was not found.', class_basename($previous->getModel()), implode(', ', $previous->getIds()))
                : 'The requested resource does not exist.';

            return response()->json(['message' => $message], 404);
        });

        $exceptions->render(function (HttpExceptionInterface $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json(
                ['message' => $exception->getMessage() ?: 'HTTP error.'],
                $exception->getStatusCode(),
                $exception->getHeaders(),
            );
        });
    })->create();
