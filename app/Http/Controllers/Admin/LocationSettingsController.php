<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Employee;
use Illuminate\Http\Request;

class LocationSettingsController extends Controller
{
    public function index()
    {
        $employees = Employee::where('status', 'active')->get();
        $locations = Location::with('employee')->get()->keyBy('emp_id');
        
        // return $locations;
        return view('admin.location-settings.index', compact('employees', 'locations'));
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
}