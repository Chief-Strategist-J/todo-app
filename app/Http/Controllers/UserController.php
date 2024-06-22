<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserLoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Throwable;

use function App\Helper\errorMsg;
use function App\Helper\successMessage;

class UserController extends Controller
{
    public function login(UserLoginRequest $request): JsonResponse
    {
        try {
            $credentials = $request->only('email', 'password');
            $user = Cache::remember($credentials['email'], now()->addWeek(), function () use ($credentials) {
                return User::where('email', $credentials['email'])->first();
            });

            if (!is_null($user)) {
                $authenticated = Auth::attempt($credentials);
                if ($authenticated) return $this->generateLoginResponse($user, true);
                return errorMsg(message: 'User exists but the password is incorrect. Please check again');
            }

            $registerUser = [
                'name' => $request->input('name'),
                'email' => $credentials['email'],
                'password' => Hash::make($credentials['password']),
            ];

            $user = User::create($registerUser);
            return $this->generateLoginResponse($user);
        } catch (Throwable $e) {
            report($e);
            Log::info("Error while login");
            throw $e;
        }
    }

    private function generateLoginResponse($user, $isAuthenticated = false): JsonResponse
    {
        return successMessage(data: [
            'is_login' => $isAuthenticated,
            'access_token' => 'Bearer ' . $user->createToken('auth_token')->plainTextToken,
            'user_info' => $user,
        ]);
    }

    public function signOut()
    {
        Session::flush();
        Auth::logout();
    }
}
