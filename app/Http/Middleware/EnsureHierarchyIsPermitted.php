<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Models\{Project, Objective, Task};

class EnsureHierarchyIsPermitted
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $target = $this->resolveTarget($request);

        if (!$target) return $next($request);

        $errorModel = $this->hasRestrictedAncestors($target);

        if ($errorModel) {
            $errorModelName = class_basename($errorModel);
            return response()->json([
                'message' => "Can't modify resource; {$errorModelName} is {$errorModel->status->name}"
            ], 403);
        }

        return $next($request);
    }

    private function resolveTarget($request)
    {
        if ($request->isMethod('POST')) {
            if ($request->has('project_id')) return Project::find($request->project_id);
            if ($request->has('objective_id')) return Objective::find($request->objective_id);
        }

        return collect($request->route()->parameters())->first();
    }

    private function hasRestrictedAncestors($model)
    {
        if (isset($model->status) && $model->status->isRestricted()) {
            return $model;
        }

        if ($model instanceof Task) {
            return $this->hasRestrictedAncestors($model->objective);
        }

        if ($model instanceof Objective) {
            return $this->hasRestrictedAncestors($model->project);
        }

        return null;
    }
}
