<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserDetailRequest;
use App\Http\Requests\UserLoginRequest;
use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
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

            if ($isSignUp) {
                Cache::forget($credentials['email']);
            }

            $user = Cache::remember($credentials['email'], now()->addWeek(), function () use ($credentials) {
                return User::where('email', $credentials['email'])->first();
            });

            if ($user) {
                if (Auth::attempt($credentials)) {
                    $this->sendWelcomeNotification(
                        title: "Welcome",
                        message: 'Welcome back to Todo!',
                        emails: $credentials['email']
                    );

                    return $this->generateLoginResponse($user, true, isSignUp: $isSignUp);
                } else {
                    return response()->json([
                        'message' => 'User exists but the password is incorrect. Please check again'
                    ], 401);
                }
            }

            $registerUser = [
                'name' => $credentials['email'],
                'email' => $credentials['email'],
                'password' => Hash::make($credentials['password']),
            ];

            $user = User::create($registerUser);

            $this->sendWelcomeNotification(
                title: "Welcome",
                message: 'Welcome to Todo for the first time!',
                emails: $credentials['email']
            );

            UserDetail::updateOrCreate(
                ['user_id' => $user->id],
                ['email' => $credentials['email']]
            );

            Auth::login($user);
            return $this->generateLoginResponse($user, isSignUp: $isSignUp);
        }catch (QueryException $e) { // Catch specific DB exception
            report($e);
            Log::error("Database error while login: " . $e->getMessage());
            return response()->json(['message' => 'Internal Server Error. Please try again later.'], 500);
        }  catch (Throwable $e) {
            report($e);
            Log::error("Error While login");
            return response()->json(['message' => 'Internal Server Error. Please try again later.'], 500);
        }
    }


    public function sendWelcomeNotification($title, $message, $emails)
    {

        $url = 'https://onesignal.com/api/v1/notifications';

        $header = [
            'Authorization' => 'Basic ' . env('ONE_SIGNAL_REST_KEY'),
            'Content-Type' => 'application/json',
        ];

        $req = [
            'app_id' => env('ONE_SIGNAL_APP_ID'),
            'headings' => ['en' => $title],
            'contents' => ['en' => $message],
            'filters' => [
                [
                    'field' => 'tag',
                    'key' => 'email',
                    'relation' => '=',
                    'value' => $emails,
                ],
            ],

        ];

        Http::withHeaders($header)->post($url, $req);
    }


    public function createOtp(UpdateUserDetailRequest $request): JsonResponse
    {
        try {
            $user = $request->user();
            $userId = $request->input('user_id');

            if ($user && $userId && $user->id != $userId) {
                return $this->bearerNotMatched($request);
            }

            $cachedOtpId = '_otp_' . $userId;
            $cachedOtp = Cache::get($cachedOtpId);

            if ($cachedOtp) {
                return $this->otpIsSended($cachedOtp);
            }

            $otp = str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
            Cache::put($cachedOtpId, $otp, now()->addMinutes(2));

            return $this->otpIsSended($otp);
        } catch (Throwable $e) {
            report($e);
            Log::info("Error While Creating Otp");
            throw $e;
        }
    }

    public function verifyOtp(UpdateUserDetailRequest $request): JsonResponse
    {
        try {
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

            return $this->otpVerificationFailed();
        } catch (Throwable $e) {
            report($e);
            Log::info("Error While Veryfing Otp");
            throw $e;
        }
    }

    public function updateUserDetails(UpdateUserDetailRequest $request): JsonResponse
    {
        try {
            $user = $request->user();
            $userId = $request->input('user_id');

            if ($user && $userId && $user->id != $userId) {
                return $this->bearerNotMatched($request);
            }

            $fields = [
                'first_name',
                'last_name',
                'phone',
                'birthdate',
                'address',
                'city',
                'state',
                'country',
                'zipcode',
                'avatar',
                'bio',
                'is_active',
                'user_id',
                'firebase_user_details_id '
            ];

            $cacheKey = '_user_detail_' . $userId;
            Cache::forget($cacheKey);
            $userDetail = UserDetail::updateOrCreate(
                ['user_id' => $request->input('user_id')],
                collect($request->only($fields))->filter()->all()
            );

            return successMessage(data: ['user_info' => $userDetail]);
        } catch (Throwable $e) {
            report($e);
            Log::info("Error While Updating User Details");
            throw $e;
        }
    }


    public function getUserDetail(UpdateUserDetailRequest $request): JsonResponse
    {

        try {
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
                return $this->userNotFound();
            }

            return successMessage(data: ['user_info' => $info]);
        } catch (Throwable $e) {
            report($e);
            Log::info("Error While Getting User Details");
            throw $e;
        }
    }



    public function updatePassword(Request $request)
    {
        try {
            $email = $request->input('email');
            $password = $request->input('password');

            $user = User::where('email', $email)->first();

            if (is_null($user))
                return $this->userNotFound();

            $user->password = Hash::make($password);
            $user->save();

            return successMessage(
                data: [
                    'success' => true,
                    'message' => 'Password updated successfully.',
                ],
            );
        } catch (Throwable $e) {
            report($e);
            Log::info("Error While Updating Password");
            throw $e;
        }
    }

    public function forgetPassword(Request $request): JsonResponse
    {

        try {
            $email = $request->input('email');
            $user = UserDetail::where('email', $email)->first();

            if (is_null($user))
                return $this->userNotFound();

            $cachedOtpId = '_otp_for_forgetPassword_' . $email;
            $cachedOtp = Cache::get($cachedOtpId);

            if ($cachedOtp)
                return $this->otpIsSended($cachedOtp);

            $otp = str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
            Cache::put($cachedOtpId, $otp, now()->addMinutes(2));

            return $this->otpIsSended($otp);
        } catch (Throwable $e) {
            report($e);
            Log::info("Error While Forgetting Password");
            throw $e;
        }
    }

    public function verifyPasswordOtp(Request $request): JsonResponse
    {
        try {
            $request->validate(['otp' => 'required|digits:6']);

            $email = $request->input('email');
            $otp = $request->input('otp');

            $cachedOtpId = '_otp_for_forgetPassword_' . $email;
            $cachedOtp = Cache::get($cachedOtpId);

            if ($cachedOtp && $cachedOtp === $otp) {
                Cache::forget($cachedOtpId);

                $user = User::where('email', $email)->first();
                $userDetail = UserDetail::where('email', $email)->first();

                return successMessage(
                    data: [
                        'success' => true,
                        'message' => 'OTP is verified.',
                        'access_token' => 'Bearer ' . $user->createToken('auth_token')->plainTextToken,
                        'data' => $userDetail,
                    ],
                );
            }

            return $this->otpVerificationFailed();
        } catch (Throwable $e) {
            report($e);
            Log::info("Error While Verifing Password");
            throw $e;
        }
    }

    private function userNotFound()
    {
        return successMessage(
            data: [
                'success' => false,
                'message' => 'User not found.',
            ]
        );
    }

    private function otpIsSended($otp): JsonResponse
    {
        return successMessage(
            data: [
                'success' => true,
                'message' => 'OTP Is Sent Successfully.',
                'otp' => $otp,
            ]
        );
    }

    private function otpVerificationFailed(): JsonResponse
    {
        return successMessage(
            data: [
                'success' => false,
                'message' => 'OTP Verification Failed.',
            ]
        );
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
                'message' => 'User ID and Bearer Token Mismatch.'
            ],
        );
    }

    public function signOut()
    {
        Session::flush();
        Auth::logout();
    }
}
