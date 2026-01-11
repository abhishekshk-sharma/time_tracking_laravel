<?php

namespace App\Services;

use App\Models\Location;
use App\Models\Employee;
use App\Models\EntryImage;
use App\Models\TimeEntry;
use Illuminate\Http\Request;

class LocationAuthService
{
    public function authenticateLocation($employee, Request $request)
    {
        // Find employee's admin
        $admin = $this->findEmployeeAdmin($employee);
        
        if (!$admin) {
            return ['success' => false, 'message' => 'No admin assigned'];
        }

        // Get admin's location settings
        $adminLocation = Location::where('emp_id', $admin->emp_id)->first();

        if (!$adminLocation) {
            // No location settings - require image capture
            return $this->requireImageCapture();
        }

        $hasIp = !empty($adminLocation->ip_address);
        $hasCoords = !empty($adminLocation->latitude) && !empty($adminLocation->longitude);

        if (!$hasIp && !$hasCoords) {
            // No settings - require image capture
            return $this->requireImageCapture();
        }

        $ipValid = false;
        $coordsValid = false;

        // Check IP if set
        if ($hasIp) {
            $employeeIp = $request->ip();
            $ipValid = $this->validateIpAddress($employeeIp, $adminLocation->ip_address);
        }

        // Check coordinates if set
        if ($hasCoords) {
            $employeeLat = $request->input('latitude');
            $employeeLng = $request->input('longitude');
            
            if ($employeeLat && $employeeLng) {
                $range = $adminLocation->range ?? 100; // Use range from database or default to 100m
                $coordsValid = $this->validateCoordinates(
                    $employeeLat, 
                    $employeeLng, 
                    $adminLocation->latitude, 
                    $adminLocation->longitude,
                    $range
                );
            }
        }

        // If both are set, either one being valid is enough
        if ($hasIp && $hasCoords) {
            if ($ipValid || $coordsValid) {
                return ['success' => true, 'message' => 'Location authenticated'];
            }
            // Both failed - offer image capture as fallback
            return $this->requireImageCapture();
        }
        // If only IP is set
        elseif ($hasIp && !$hasCoords) {
            if ($ipValid) {
                return ['success' => true, 'message' => 'IP authenticated'];
            }
            // IP failed - offer image capture as fallback
            return $this->requireImageCapture();
        }
        // If only coordinates are set
        elseif (!$hasIp && $hasCoords) {
            if ($coordsValid) {
                return ['success' => true, 'message' => 'Location authenticated'];
            }
            // Coordinates failed - offer image capture as fallback
            return $this->requireImageCapture();
        }

        return $this->requireImageCapture();
    }

    private function findEmployeeAdmin($employee)
    {
        // Find admin in same department or region
        return Employee::where('role', 'admin')
            ->where('status', 'active')
            ->where(function($query) use ($employee) {
                $query->where('department_id', $employee->department_id)
                      ->orWhere('region_id', $employee->region_id);
            })
            ->first();
    }

    private function requireImageCapture()
    {
        return [
            'success' => false, 
            'require_image' => true, 
            'message' => 'Image capture required'
        ];
    }

    private function validateIpAddress($employeeIp, $adminIp)
    {
        return $employeeIp === $adminIp;
    }

    private function validateCoordinates($empLat, $empLng, $adminLat, $adminLng, $range = 100)
    {
        // Calculate distance using Haversine formula
        $distance = $this->calculateDistance($empLat, $empLng, $adminLat, $adminLng);
        
        // Convert range from meters to kilometers
        $rangeInKm = $range / 1000;
        
        return $distance <= $rangeInKm;
    }

    private function calculateDistance($lat1, $lng1, $lat2, $lng2)
    {
        $earthRadius = 6371; // Earth's radius in kilometers

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng/2) * sin($dLng/2);

        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        $distance = $earthRadius * $c;

        return $distance;
    }

    public function storeEntryImage($employee, $imageData, $entryType, $entryId = null)
    {
        $imageName = $employee->emp_id . '_' . $entryType . '_' . time() . '.jpg';
        $imagePath = public_path('entry_images/' . $imageName);
        
        // Create directory if it doesn't exist
        if (!file_exists(public_path('entry_images'))) {
            mkdir(public_path('entry_images'), 0755, true);
        }

        // Decode and save image
        $imageData = str_replace('data:image/jpeg;base64,', '', $imageData);
        $imageData = str_replace(' ', '+', $imageData);
        $decodedImage = base64_decode($imageData);
        
        file_put_contents($imagePath, $decodedImage);

        // Store in database
        EntryImage::create([
            'entry_id' => $entryId,
            'emp_id' => $employee->emp_id,
            'entry_type' => $entryType,
            'entry_time' => now(),
            'imageFile' => $imageName
        ]);

        return ['success' => true, 'message' => 'Image captured successfully'];
    }
}