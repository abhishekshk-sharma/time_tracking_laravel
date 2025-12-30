<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SessionController extends Controller
{
    public function check(Request $request)
    {
        $info = $request->input('info');
        
        if ($info == "check") {
            // Check if user is authenticated
            if (!Auth::check()) {
                return response('expired');
            }
            
            // Check session timeout (5 minutes)
            $lastActivity = session('last_activity');
            if ($lastActivity && Carbon::now()->diffInMinutes(Carbon::parse($lastActivity)) > 5) {
                Auth::logout();
                session()->flush();
                return response('expired');
            }
            
            // Update last activity
            session(['last_activity' => Carbon::now()]);
            
            return response('active');
        }
        
        return response('error');
    }
}