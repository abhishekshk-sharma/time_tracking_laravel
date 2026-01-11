<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Employee;
use Illuminate\Http\Request;

class LocationSettingsController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $perPage = $request->get('per_page', 10);
        
        $adminsQuery = Employee::where('role', 'admin')->where('status', 'active');
        
        if ($search) {
            $adminsQuery->where(function($q) use ($search) {
                $q->where('username', 'like', '%' . $search . '%')
                  ->orWhere('emp_id', 'like', '%' . $search . '%');
            });
        }
        
        $admins = $adminsQuery->paginate($perPage);
        $locations = Location::with('employee')->get()->keyBy('emp_id');
        
        return view('super-admin.location-settings.index', compact('admins', 'locations'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'emp_id' => 'required|exists:employees,emp_id',
            'ip_address' => 'nullable|ip',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'range' => 'nullable|integer|min:1'
        ]);

        Location::updateOrCreate(
            ['emp_id' => $request->emp_id],
            [
                'ip_address' => $request->ip_address,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'range' => $request->range ?? 100
            ]
        );

        return redirect()->back()->with('success', 'Location settings updated successfully.');
    }

    public function edit($empId)
    {
        $admin = Employee::where('emp_id', $empId)->where('role', 'admin')->firstOrFail();
        $location = Location::where('emp_id', $empId)->first();
        
        return response()->json([
            'admin' => $admin,
            'location' => $location
        ]);
    }
}