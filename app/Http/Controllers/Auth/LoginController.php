<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Otp;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

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
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except(['logout', 'verifyOtp']);
        $this->middleware('auth')->only('logout');
    }

    // Override the login method
    public function login(Request $request)
    {
        $this->validateLogin($request);

        // Check if user exists
        $user = User::where('email', $request->email)->first();
        if (!$user || !password_verify($request->password, $user->password)) {
            return $this->sendFailedLoginResponse($request);
        }

        // Generate and send OTP
        $otp = $this->generateOtp($request->email);
        $this->sendOtpEmail($request->email, $otp);

        // Store email in session for OTP verification
        $request->session()->put('auth_email', $request->email);

        return redirect()->route('otp.verify.form');
    }

    private function generateOtp($email)
    {
        // Generate 6-digit OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store hashed OTP in database for security
        Otp::updateOrCreate(
            ['email' => $email],
            [
                'otp' => hash('sha256', $otp),
                'expires_at' => Carbon::now()->addMinutes(5)
            ]
        );

        return $otp;
    }

    private function sendOtpEmail($email, $otp)
    {
        // Log OTP for demo purposes (remove in production)
        if (app()->environment('local') && str_contains($email, '@demo.com')) {
            \Log::info('DEMO OTP Generated', [
                'email' => $email,
                'otp' => $otp,
                'message' => 'Use this OTP for demo login'
            ]);
        }

        // Send email with OTP
        Mail::send('emails.otp', ['otp' => $otp], function ($message) use ($email) {
            $message->to($email)
                ->subject('Your OTP for Login');
        });
    }

    public function showOtpForm()
    {
        if (!session('auth_email')) {
            return redirect()->route('login');
        }

        return view('auth.otp');
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|numeric|digits:6'
        ]);

        $email = session('auth_email');
        if (!$email) {
            return redirect()->route('login');
        }

        // Get all non-expired OTPs for this email
        $otpData = Otp::where('email', $email)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if (!$otpData) {
            return back()->withErrors(['otp' => 'Invalid or expired OTP']);
        }

        // Verify OTP using hash comparison
        $hashedInputOtp = hash('sha256', $request->otp);
        if (!hash_equals($otpData->otp, $hashedInputOtp)) {
            // Log failed OTP attempt
            \Log::warning('Failed OTP verification attempt', [
                'email' => $email,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return back()->withErrors(['otp' => 'Invalid or expired OTP']);
        }

        // OTP is valid, log the user in
        $user = User::where('email', $email)->first();
        Auth::login($user);

        // Clear session and OTP
        $request->session()->forget('auth_email');
        $otpData->delete();

        return redirect()->intended($this->redirectPath());
    }

    public function resendOtp(Request $request)
    {
        $email = session('auth_email');
        if (!$email) {
            return redirect()->route('login');
        }

        $otp = $this->generateOtp($email);
        $this->sendOtpEmail($email, $otp);

        return back()->with('status', 'OTP has been resent');
    }
}
