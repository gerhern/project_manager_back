<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Models\{Project, Objective, Task};
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class EnsureHierarchyIsPermitted
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
            if ($request->isMethod('GET')) {
            return $next($request);
        }

        // Obtenemos todos los modelos inyectados en la URL (Project, Objective, Task)
        $parameters = $request->route()->parameters();

        foreach ($parameters as $model) {
            if (isset($model->status) && $model->status->isRestricted()) {
                $modelName = class_basename($model);
                throw new AccessDeniedHttpException(
                    "Can't modify resource; {$modelName} is {$model->status->name}"
                );
            }
        }

        return $next($request);
    }
}
