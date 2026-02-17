<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole();
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $employee = Employee::where('username', $request->username)->first();

        if (!$employee || !Hash::check($request->password, $employee->password_hash)) {
            return back()->withErrors([
                'username' => 'Invalid username or password.',
            ])->withInput();
        }

        if ($employee->status !== 'active') {
            return back()->withErrors([
                'username' => 'Your account is inactive. Please contact administrator.',
            ])->withInput();
        }

        // Auth::login($employee);

        if (!Auth::attempt([
            "username" => $request->input('username'),
            "password" => $request->input('password')
        ])) {
            throw ValidationException::withMessages([
                'username' => ['The provided credentials are incorrect.'],
            ]);
        }

        session(['last_activity' => now()]);
        session(['at_office' => true]);

        $output = ['success' => true, 'data' => _token];
        return  json_encode($output);
        // return $this->redirectBasedOnRole();
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function redirectBasedOnRole()
    {
        if (Auth::user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('dashboard');
    }

    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $employee = Employee::where('email', $request->email)->first();

        if (!$employee) {
            return back()->withErrors(['email' => 'We can\'t find a user with that email address.']);
        }

        $token = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'email' => $request->email,
                'token' => Hash::make($token),
                'created_at' => Carbon::now()
            ]
        );

        $resetUrl = route('password.reset', ['token' => $token]) . '?email=' . urlencode($request->email);

        try {
            Mail::send('emails.password-reset', ['resetUrl' => $resetUrl, 'employee' => $employee], function($message) use($request) {
                $message->to($request->email);
                $message->subject('Reset Password');
            });

            return back()->with('status', 'We have emailed your password reset link!');
        } catch (\Exception $e) {
            return back()->withErrors(['email' => 'Failed to send reset email. Please try again.']);
        }
    }

    public function showResetPassword(Request $request, $token)
    {
        return view('auth.reset-password', ['token' => $token, 'email' => $request->email]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $passwordReset = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$passwordReset || !Hash::check($request->token, $passwordReset->token)) {
            return back()->withErrors(['email' => 'This password reset token is invalid.']);
        }

        if (Carbon::parse($passwordReset->created_at)->addMinutes(60)->isPast()) {
            return back()->withErrors(['email' => 'This password reset token has expired.']);
        }

        $employee = Employee::where('email', $request->email)->first();
        $employee->password_hash = Hash::make($request->password);
        $employee->save();

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('login')->with('status', 'Your password has been reset!');
    }
}