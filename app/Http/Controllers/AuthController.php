<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole();
        }

        return view('login');
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

        Auth::login($employee);
        session(['last_activity' => now()]);
        session(['at_office' => true]);

        return $this->redirectBasedOnRole();
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
}