<?php

namespace App\Http\Controllers;
use App\Models\Employee;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    public function index()
    {
        $employee = Employee::with("region")->where('emp_id', '=', Auth::user()->emp_id)->first();
        // $region = $region->first()->region->name;
        $ff =  $employee->region->name??"N/A";
        return view('profile', compact('ff'));
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

    public function updateProfile(Request $request)
    {
        $click = $request->input('click');
        
        switch ($click) {
            case 'changeemail':
                return $this->updateEmail($request);
            case 'changepass':
                return $this->updatePassword($request);
            case 'changdob':
                return $this->updateDob($request);
            default:
                return response('invalid_action', 400);
        }
    }
}