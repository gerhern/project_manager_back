<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Models\Objective;
use Illuminate\Support\Facades\Gate;

class ObjectiveController extends Controller
{
    use ApiResponse;
    public function update(Request $request, Objective $objective){

        Gate::authorize('updateObjective', $objective);
        return $this->sendApiResponse($objective, 'Objective updated successfully');
    }
}
