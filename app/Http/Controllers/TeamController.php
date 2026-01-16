<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    use ApiResponse;
    public function index(Request $request){
        $teams = $request->user()->teams()->withPivot('role_id')->get();
        return $this->sendApiResponse($teams, "Data Retrieved Successfuly", 200);
    }
}
