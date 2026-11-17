<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use App\Models\User;
use App\Models\Setting;

class AuthController extends Controller
{
    /**
     * API Login with subscription check
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $credentials = $request->only('email', 'password');

        // Attempt authentication
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            
            // Check if user is admin
            if ($user->is_admin == 1 && $user->super_admin == 1) {
                // Super admin - no subscription check needed
                $token = $user->createToken('auth_token')->plainTextToken;
                
                return response()->json([
                    'success' => true,
                    'message' => 'Login successful',
                    'data' => [
                        'user' => [
                            'id' => $user->id,
                            'name' => $user->name,
                            'email' => $user->email,
                            'active' => $user->active,
                        ],
                        'token' => $token,
                        'token_type' => 'Bearer'
                    ]
                ], 200);
            } else {
                // Check subscription for non-admin users
                $subscriptionExpired = $this->checkSubscriptionExpiry($user);
                
                if ($subscriptionExpired) {
                    // Logout the user
                    Auth::logout();
                    
                    // Get admin contact info from settings
                    $setting = Setting::pluck('value', 'name')->toArray();
                    $adminPhone = isset($setting['phone']) ? $setting['phone'] : 'N/A';
                    $adminEmail = isset($setting['email']) ? $setting['email'] : 'admin@domain.com';
                    
                    return response()->json([
                        'success' => false,
                        'message' => 'Subscription expired',
                        'data' => [
                            'subscription_expired' => true,
                            'admin_phone' => $adminPhone,
                            'admin_email' => $adminEmail,
                            'message' => 'Your subscription has expired. Please contact the administrator to renew your subscription.'
                        ]
                    ], 403);
                }
                
                // Subscription is valid - proceed with login
                $token = $user->createToken('auth_token')->plainTextToken;
                
                return response()->json([
                    'success' => true,
                    'message' => 'Login successful',
                    'data' => [
                        'user' => [
                            'id' => $user->id,
                            'name' => $user->name,
                            'email' => $user->email,
                            'active' => $user->active,
                            'user_id' => $user->user_id, // Parent user ID if any
                        ],
                        'token' => $token,
                        'token_type' => 'Bearer'
                    ]
                ], 200);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Authentication failed. Email or password is invalid.',
                'data' => null
            ], 401);
        }
    }

    /**
     * API Logout
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        // Revoke the token that was used to authenticate the current request
        $request->user()->currentAccessToken()->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Successfully logged out',
            'data' => null
        ], 200);
    }

    /**
     * Get authenticated user profile
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function profile(Request $request)
    {
        $user = $request->user();
        
        return response()->json([
            'success' => true,
            'message' => 'User profile retrieved successfully',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'active' => $user->active,
                    'user_id' => $user->user_id,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ]
            ]
        ], 200);
    }

    /**
     * Update user profile
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();
        
        // Validate request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Update user profile
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'active' => $user->active,
                    'user_id' => $user->user_id,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ]
            ]
        ], 200);
    }

    /**
     * Update user password
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePassword(Request $request)
    {
        $user = $request->user();
        
        // Validate request
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
            'new_password_confirmation' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if current password is correct
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect',
                'data' => null
            ], 400);
        }

        // Update password
        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully',
            'data' => null
        ], 200);
    }

    /**
     * Send password reset link
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function forgotPassword(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Send password reset link
        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'success' => true,
                'message' => 'Password reset link sent to your email',
                'data' => null
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Unable to send password reset link',
                'data' => null
            ], 500);
        }
    }

    /**
     * Reset password
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetPassword(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Reset password
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'success' => true,
                'message' => 'Password reset successfully',
                'data' => null
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Unable to reset password',
                'data' => null
            ], 500);
        }
    }

    /**
     * Check if user's subscription has expired
     *
     * @param User $user
     * @return bool
     */
    private function checkSubscriptionExpiry($user)
    {
        // If user has a parent (user_id is not null), check parent's subscription
        if ($user->user_id !== null) {
            $parentUser = User::find($user->user_id);
            if ($parentUser) {
                return $this->checkUserSubscriptionExpiry($parentUser);
            }
        } else {
            // Check user's own subscription
            return $this->checkUserSubscriptionExpiry($user);
        }
        
        return false;
    }

    /**
     * Check if a specific user's subscription has expired
     *
     * @param User $user
     * @return bool
     */
    private function checkUserSubscriptionExpiry($user)
    {
        // Get the most recent active subscription
        $activeSubscription = $user->subscriptionHistory()
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->latest()
            ->first();
        
        // If no active subscription found, check if there's any expired subscription
        if (!$activeSubscription) {
            return true;
        }

        return false;
    }
}
