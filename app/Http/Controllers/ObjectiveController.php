<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Objective;
use Illuminate\Support\Facades\Gate;

class ObjectiveController extends Controller
{
    public function update(Request $request, Objective $objective){

        Gate::authorize('updateObjective', $objective);
        return response()->json([
            'message' => 'Objective updated successfully',
            'objective' => $objective
        ], 200);
    }
}
