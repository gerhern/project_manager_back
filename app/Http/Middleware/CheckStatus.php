<?php

namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $resource = collect($request->route()->parameters())->first();

        if ($resource && isset($resource->status)) {
            $restrictedNames = ['Canceled', 'Completed', 'CancelInProgress'];

            if (in_array($resource->status->name, $restrictedNames)) {
                return response()->json([
                    'success' => false,
                    'data' => [],
                    'message' => "Can't modify resource in status: {$resource->status->name}"
                ], 403);
            }
        }

        return $next($request);
    }
}
