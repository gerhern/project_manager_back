<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use ApiResponse;
    public function login(Request $request): JsonResponse {
        $request->validate([
            'email'     => 'required|email|string',
            'password'  => 'required|string|min:8'
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return $this->sendApiError('Invalid data', 401);
        }

        $token = $user->createToken('live-demo-token')->plainTextToken;

        return  $this->sendApiResponse(['user' => New UserResource($user), 'token' => $token], 'Login successfull');
    }
}
