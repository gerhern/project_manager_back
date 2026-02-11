<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->statefulApi();
        $middleware->validateCsrfTokens(except: ['*']);
    })
    ->withExceptions(function (Exceptions $exceptions): void {

    $exceptions->render(function (ValidationException $e, $request) {
        return response()->json([
            'success' => false,
            'message' => 'The given data was invalid.',
            'errors'  => $e->errors(),
        ], 422);
    });
    
        // Error 403
    $exceptions->render(function (AccessDeniedHttpException $e, $request) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage() ?: "Forbidden access.",
        ], 403);
    });

    // Error 401
    $exceptions->render(function (AuthenticationException $e, $request) {
        return response()->json([
            'success' => false,
            'message' => "Unauthenticated. Please log in.",
        ], 401);
    });

    // Error 404
    $exceptions->render(function (NotFoundHttpException $e, $request) {
        return response()->json([
            'success' => false,
            'message' => "Resource not found.",
            'code'    => 'APPE'
        ], 404);
    });

    $exceptions->shouldRenderJsonWhen(function ($request, $e) {
            return true; 
        });

    $exceptions->render(function (Throwable $e, $request) {
        if ($request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => config('app.debug') ? $e->getMessage() : 'Server Error.',
            ], 500);
        }
    });
        //
    })->create();
