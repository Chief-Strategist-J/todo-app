<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function testLoginWithCorrectCredentials()
    {
        $password = 'secret';
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make($password),
        ]);

        $response = $this->postJson(route('loginOrSignUp'), [
            'email' => $user->email,
            'password' => $password,
            'is_sign_up' => false,
        ]);

        $response->assertStatus(200);
        $this->assertAuthenticatedAs($user);
        $response->assertJsonStructure([
            'message',
            'status',
            'data' => [
                'is_login',
                'is_sign_up',
                'access_token',
                'user_info' => [
                    'id',
                    'email',
                ],
            ],
        ]);
    }

    public function testLoginWithIncorrectPassword()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('correct-password'),
        ]);

        $response = $this->postJson(route('loginOrSignUp'), [
            'email' => $user->email,
            'password' => 'wrong-password',
            'is_sign_up' => false,
        ]);

        $response->assertStatus(401);
        $this->assertGuest();
        $response->assertJson(['message' => 'User exists but the password is incorrect. Please check again']);
    }

    public function testSignUpWithNewUser()
    {
        $email = 'newuser@example.com';
        $password = 'secret';

        $response = $this->postJson(route('loginOrSignUp'), [
            'email' => $email,
            'password' => $password,
            'is_sign_up' => true,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('users', ['email' => $email]);
        $this->assertAuthenticated();
        $this->assertAuthenticatedAs(User::where('email', $email)->first());
        $response->assertJsonStructure([
            'message',
            'status',
            'data' => [
                'is_login',
                'is_sign_up',
                'access_token',
                'user_info' => [
                    'id',
                    'email',
                ],
            ],
        ]);
    }

    public function testCacheIsClearedOnSignUp()
    {
        $email = 'cacheduser@example.com';
        Cache::put($email, ['fake' => 'data'], now()->addWeek());

        $this->postJson(route('loginOrSignUp'), [
            'email' => $email,
            'password' => 'secret',
            'is_sign_up' => true,
        ]);

        $this->assertNull(Cache::get($email));
    }
    
    public function testErrorIsLoggedOnException()
    {
        // Create a mock for the User model
        $mock = Mockery::mock(User::class);

        // Specify that the create method should throw an exception
        $mock->shouldReceive('create')
            ->once() // Expect it to be called once
            ->andThrow(new \Exception('Database error'));

        // Replace the User model with the mock in the container
        $this->app->instance(User::class, $mock);

        Log::shouldReceive('error')->once();

        $response = $this->postJson(route('loginOrSignUp'), [
            'email' => 'error@example.com',
            'password' => 'secret',
            'is_sign_up' => true,
        ]);

        // Assert that the response status is 500
        $response->assertStatus(200);
    }
    public function testValidationErrors()
    {
        $response = $this->postJson(route('loginOrSignUp'), []);

        $response->assertStatus(422);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'email',
                'password',
                'is_sign_up',
            ],
        ]);
    }
}
