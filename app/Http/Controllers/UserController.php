<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserDetailRequest;
use App\Http\Requests\UserLoginRequest;
use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Http\Request;
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
    public function loginOrSignUp(UserLoginRequest $request): JsonResponse
    {
        try {
            $credentials = $request->only('email', 'password');
            $isSignUp = $request->input('is_sign_up');

            if ($request->filled('is_sign_up') && $request->input('is_sign_up')) {
                Cache::forget($request->input('email'));
            }

            $user = Cache::remember($credentials['email'], now()->addWeek(), function () use ($credentials) {
                return User::where('email', $credentials['email'])->first();
            });

            if (!is_null($user)) {
                $authenticated = Auth::attempt($credentials);
                if ($authenticated)
                    return $this->generateLoginResponse($user, true, isSignUp: $isSignUp);
                errorMsg(message: 'User exists but the password is incorrect. Please check again');
            }

            $registerUser = [
                'name' => $request->input('name'),
                'email' => $credentials['email'],
                'password' => Hash::make($credentials['password']),
            ];

            $user = User::create($registerUser);

            $userDetail = UserDetail::updateOrCreate(
                ['user_id' => $user->id],
                ['email' => $credentials['email']]
            );


            return $this->generateLoginResponse($user, isSignUp: $isSignUp);
        } catch (Throwable $e) {
            report($e);
            Log::info("Error While login");
            throw $e;
        }
    }



    public function createOtp(UpdateUserDetailRequest $request): JsonResponse
    {
        $user = $request->user();
        $userId = $request->input('user_id');

        if ($user && $userId && $user->id != $userId) {
            return $this->bearerNotMatched($request);
        }

        $cachedOtpId = '_otp_' . $userId;
        $cachedOtp = Cache::get($cachedOtpId);

        if ($cachedOtp) {
            return successMessage(
                data: [
                    'success' => true,
                    'message' => 'OTP exists and is valid.',
                    'otp' => $cachedOtp,
                ]
            );
        }

        $otp = str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
        Cache::put($cachedOtpId, $otp, now()->addMinutes(2));

        return successMessage(
            data: [
                'success' => true,
                'message' => 'OTP created and sent successfully.',
                'otp' => $otp,
            ]
        );
    }

    public function verifyOtp(UpdateUserDetailRequest $request): JsonResponse
    {
        $user = $request->user();
        $userId = $request->input('user_id');

        if ($user && $userId && $user->id != $userId) {
            return $this->bearerNotMatched($request);
        }

        $request->validate(['otp' => 'required|digits:6']);

        $cachedOtpId = '_otp_' . $userId;
        $cachedOtp = Cache::get($cachedOtpId);
        $otp = $request->input('otp');

        if ($cachedOtp && $cachedOtp === $otp) {
            Cache::forget($cachedOtpId);
            return successMessage(
                data: [
                    'success' => true,
                    'message' => 'OTP is verified.',
                    'otp' => $otp,
                ],
            );
        }

        return successMessage(
            data: [
                'success' => false,
                'message' => 'OTP verification failed.',
            ]
        );
    }

    public function updateUserDetails(UpdateUserDetailRequest $request): JsonResponse
    {
        $user = $request->user();
        $userId = $request->input('user_id');

        if ($user && $userId && $user->id != $userId) {
            return $this->bearerNotMatched($request);
        }

        $fields = [
            'first_name', 'last_name', 'phone', 'birthdate', 'address', 'city',
            'state', 'country', 'zipcode', 'avatar', 'bio', 'is_active', 'user_id',
            'firebase_user_details_id '
        ];

        $cacheKey = '_user_detail_' . $userId;
        Cache::forget($cacheKey);
        $userDetail = UserDetail::updateOrCreate(
            ['user_id' => $request->input('user_id')],
            collect($request->only($fields))->filter()->all()
        );

        return successMessage(data: ['user_info' => $userDetail]);
    }


    public function getUserDetail(UpdateUserDetailRequest $request): JsonResponse
    {

        $user = $request->user();
        $userId = $request->input('user_id');

        if ($user && $userId && $user->id != $userId) {
            return $this->bearerNotMatched($request);
        }

        $cacheKey = '_user_detail_' . $userId;
        $info = Cache::remember($cacheKey, now()->addWeek(), function () use ($userId) {
            return UserDetail::find($userId);
        });

        if (is_null($info)) {
            return successMessage(
                data: [
                    'user_info' => [],
                    'message' => 'No details fond for this user'
                ]
            );
        }

        return successMessage(
            data: [
                'user_info' => $info
            ]
        );
    }

    public function forgetPassword(Request $request)
    {
        //
    }

    public function resetPassword()
    {
        //
    }

    private function generateLoginResponse(User $user, bool $isAuthenticated = false, bool $isSignUp = false): JsonResponse
    {
        return successMessage(data: [
            'is_login' => $isAuthenticated,
            "is_sign_up" => $isSignUp,
            'access_token' => 'Bearer ' . $user->createToken('auth_token')->plainTextToken,
            'user_info' => $user,
        ]);
    }

    private function bearerNotMatched(UpdateUserDetailRequest $request)
    {
        return successMessage(
            status: 403,
            data: [
                'user_info' => [],
                'message' => 'User ID and bearer token mismatch.'
            ],
        );
    }

    public function signOut()
    {
        Session::flush();
        Auth::logout();
    }
}
