<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Objective;

class ObjectiveController extends Controller
{
    public function update(Request $request, Objective $objective){
        return response()->json([
            'message' => 'Objective updated successfully',
            'objective' => $objective
        ], 418);
    }
}
