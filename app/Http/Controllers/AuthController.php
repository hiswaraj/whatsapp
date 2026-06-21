<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{
    /**
     * Show the login home view.
     */
    public function home(): View
    {
        return view('welcome');
    }

    /**
     * API endpoint to handle user login.
     */
    public function login_api(Request $request): JsonResponse
    {
        // Validate request
        $validation = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
            'user_type' => 'required|in:admin,user'
        ]);

        if ($validation->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validation->errors()->first()
            ], 422);
        }

        $validated = $validation->validated();

        // Attempt login
        if (Auth::attempt([
            'email' => $validated['email'],
            'password' => $validated['password']
        ])) {
            $user = Auth::user();

            // Match user type with config
            $expectedUserType = match ($validated['user_type']) {
                'admin' => config('const.user_types.admin.key'),
                'user' => config('const.user_types.user.key'),
            };

            // Status 1 means active/approved, 0 means pending
            if ($user->status == 1 && $user->user_type == $expectedUserType) {
                $request->session()->regenerate();

                $redirectUrl = match ($validated['user_type']) {
                    'admin' => route('admin.dashboard'),
                    'user' => route('user.dashboard'),
                };

                return response()->json([
                    'status' => true,
                    'message' => 'Login Successful!',
                    'redirect_url' => $redirectUrl
                ]);
            } else {
                Auth::logout();
                return response()->json([
                    'status' => false,
                    'message' => 'Your account is inactive or unauthorized!'
                ], 403);
            }
        }

        return response()->json([
            'status' => false,
            'message' => 'Invalid Username or Password!'
        ], 401);
    }

    /**
     * Show the forgot password page.
     */
    public function show_forgot_password(): View
    {
        return view('forgot-password');
    }

    /**
     * API endpoint to send forgot password reset link.
     */
    public function forgot_password(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'status' => true,
                'message' => 'Password reset link has been sent to your email.',
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => __($status),
            ], 500);
        }
    }

    /**
     * Show the reset password page.
     */
    public function show_reset_password(Request $request, $token): View
    {
        return view('reset-password', ['token' => $token, 'email' => $request->query('email')]);
    }

    /**
     * API endpoint to process password reset.
     */
    public function reset_password(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'email' => 'required|email|exists:users,email',
            'password' => 'required|confirmed|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'status' => true,
                'message' => 'Your password has been reset successfully.',
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => __($status),
            ], 500);
        }
    }

    /**
     * API endpoint to handle logout.
     */
    public function logout(Request $request): JsonResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'status' => true,
            'message' => 'Logged out successfully!',
            'redirect_url' => route('home')
        ]);
    }
}
