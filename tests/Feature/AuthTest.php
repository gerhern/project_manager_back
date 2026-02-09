<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login(): void {
        $user = User::factory()->create(['password' => Hash::make('password')]);
        $response = $this->postJson(route('login'), ['email' => $user->email, 'password' => 'password']);

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['user' => ['id', 'name', 'email'],'token'],
            ]);

        $this->assertCount(1, $user->tokens);
    }

    public function test_user_cannot_login():void {
        $user = User::factory()->create(['password' => Hash::make('password')]);
        $response = $this->postJson(route('login'), ['email' => $user->email, 'password' => 'errorpass']);

        $response->assertStatus(401)
            ->assertJson(['success' => false, 'message' => 'Invalid data']);
    }

    public function test_login_request_validate_data(): void
    {
        $this->postJson(route('login'), [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        // Actuamos como el usuario usando el token generado
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson(route('logout'));

        $response->assertOk();
        
        // Verificamos que el token fue eliminado de la DB
        $this->assertCount(0, $user->fresh()->tokens);
    }

    
}
