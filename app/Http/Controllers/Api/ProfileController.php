<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class ProfileController extends Controller
{
    public function updateProfile(Request $request)
    {
        $click = $request->input('click');
        $userid = Auth::user()->emp_id;
        
        switch ($click) {
            case 'changeemail':
                return $this->changeEmail($request, $userid);
            case 'changepass':
                return $this->changePassword($request, $userid);
            case 'changdob':
                return $this->changeDateOfBirth($request, $userid);
            default:
                return response('Invalid action', 400);
        }
    }
    
    private function changeEmail(Request $request, $userid)
    {
        $newEmail = $request->input('newEmail');
        
        if (empty($newEmail)) {
            return response('Email is required', 400);
        }
        
        // Check if email already exists
        $existingEmployee = Employee::where('email', $newEmail)
            ->where('emp_id', '!=', $userid)
            ->first();
            
        if ($existingEmployee) {
            return response('Email already exists', 400);
        }
        
        $employee = Employee::where('emp_id', $userid)->first();
        if ($employee) {
            $employee->email = $newEmail;
            $employee->save();
            return response('success');
        }
        
        return response('Employee not found', 404);
    }
    
    private function changePassword(Request $request, $userid)
    {
        $currentPassword = $request->input('currentPassword');
        $newPassword = $request->input('newPassword');
        
        if (empty($currentPassword) || empty($newPassword)) {
            return response('Both current and new passwords are required', 400);
        }
        
        $employee = Employee::where('emp_id', $userid)->first();
        if (!$employee) {
            return response('Employee not found', 404);
        }
        
        // Verify current password
        if (!Hash::check($currentPassword, $employee->password_hash)) {
            return response('Current password is incorrect', 400);
        }
        
        // Update password
        $employee->password_hash = Hash::make($newPassword);
        $employee->save();
        
        return response('success');
    }
    
    private function changeDateOfBirth(Request $request, $userid)
    {
        $newDob = $request->input('newDob');
        
        if (empty($newDob)) {
            return response('Date of birth is required', 400);
        }
        
        // Validate date format and ensure it's not in the future
        try {
            $dobDate = Carbon::parse($newDob);
            if ($dobDate->isFuture()) {
                return response('Date of birth cannot be in the future', 400);
            }
        } catch (\Exception $e) {
            return response('Invalid date format', 400);
        }
        
        $employee = Employee::where('emp_id', $userid)->first();
        if ($employee) {
            $employee->dob = $dobDate;
            $employee->save();
            return response('success');
        }
        
        return response('Employee not found', 404);
    }
}