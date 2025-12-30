<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    public function index()
    {
        return view('profile');
    }

    public function updateEmail(Request $request)
    {
        $request->validate([
            'newEmail' => 'required|email|unique:employees,email,' . Auth::id(),
        ]);

        $employee = Auth::user();
        $employee->update(['email' => $request->newEmail]);

        return response('success');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'currentPassword' => 'required',
            'newPassword' => 'required|min:8',
        ]);

        $employee = Auth::user();

        if (!Hash::check($request->currentPassword, $employee->password_hash)) {
            return response('invalid_current_password');
        }

        $employee->update(['password_hash' => Hash::make($request->newPassword)]);

        return response('success');
    }

    public function updateDob(Request $request)
    {
        $request->validate([
            'newDob' => 'required|date|before:today',
        ]);

        $employee = Auth::user();
        $employee->update(['dob' => $request->newDob]);

        return response('success');
    }
}