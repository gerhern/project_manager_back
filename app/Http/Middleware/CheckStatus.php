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
        $params = $request->route()->parameters();

        $model = reset($params);

        dd($params, $model);

        if ($model && in_array($model->status->value, $restrictedStatuses)) {
            return response()->json([
                'message' => "No puedes modificar este recurso en estado: {$model->status->name}"
            ], 403);
        }

        return $next($request);
    }
}
