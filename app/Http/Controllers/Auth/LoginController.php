<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }
    public function login(Request $request)
    {
        $input = $request->all();

        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required',

        ]);

        if(auth()->attempt(array('email' => $input['email'], 'password' => $input['password'])))
        {
            $user = auth()->user();
            
            // Check if user is admin
            if ($user->is_admin == 1 and $user->super_admin ==1) {
                return redirect()->route('admin.dashboard');
            } else {
                // Check subscription for non-admin users
                $subscriptionExpired = $this->checkSubscriptionExpiry($user);
                
                if ($subscriptionExpired) {
                    // Logout the user
                    auth()->logout();
                    
                    // Get admin contact info from settings
                    $setting = \App\Models\Setting::pluck('value','name')->toArray();
                    $adminPhone = isset($setting['phone']) ? $setting['phone'] : 'N/A';
                    $adminEmail = isset($setting['email']) ? $setting['email'] : 'admin@domain.com';
                    
                    return redirect()->route('login')
                        ->with('subscription_expired', true)
                        ->with('admin_phone', $adminPhone)
                        ->with('admin_email', $adminEmail);
                }
                
                return redirect()->route('admin.dashboard');
            }
        } else {
            return redirect()->route('login')
                ->with('error','Authentication Failed. Email or Password Is Invalid.');
        }
    }

    /**
     * Check if user's subscription has expired
     *
     * @param \App\Models\User $user
     * @return bool
     */
    private function checkSubscriptionExpiry($user)
    {
        // If user has a parent (user_id is not null), check parent's subscription
        if ($user->user_id !== null) {
            $parentUser = \App\Models\User::find($user->user_id);
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
     * @param \App\Models\User $user
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
