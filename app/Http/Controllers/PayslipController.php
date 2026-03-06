<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SalaryReport;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PayslipController extends Controller
{
    public function index(Request $request)
    {
        $empId = Auth::user()->emp_id;
        $selectedYear = $request->get('year', date('Y'));
        
        // Get all available years for navigation
        $availableYears = SalaryReport::where('emp_id', $empId)
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();
            
        // Get payslips for selected year only (only released reports for employees)
        $payslips = SalaryReport::where('emp_id', $empId)
            ->where('year', $selectedYear)
            
            ->where('is_released', '=', 1)
            ->orderBy('month', 'ASC')
            ->get()
            ->map(function($payslip) {
                $payslip->month_name = Carbon::create()->month($payslip->month)->format('F');
                $payslip->formatted_date = Carbon::create()->month($payslip->month)->format('F') . ' ' . $payslip->year;
                return $payslip;
            });
            
        return view('payslips.index', compact('payslips', 'selectedYear', 'availableYears'));
    }
    
    public function download($id)
    {
        $payslip = SalaryReport::where('id', $id)
            ->where('emp_id', Auth::user()->emp_id)
            ->where('is_released', '=', 1)
            ->firstOrFail();
            
        $employee = Employee::where('emp_id', $payslip->emp_id)->first();
        
        // Convert logo to base64 for Browserless.io
        $logoPath = public_path('images/logo.png');
        $logoBase64 = '';
        if (file_exists($logoPath)) {
            $logoData = file_get_contents($logoPath);
            $logoBase64 = 'data:image/png;base64,' . base64_encode($logoData);
        }
        
        $html = view('payslips.pdf', ['salaryReport' => $payslip, 'employee' => $employee, 'logoBase64' => $logoBase64])->render();
        
        // Use Browserless.io HTTP API directly
        $browserlessUrl = env('BROWSERLESS_URL', 'https://chrome.browserless.io');
        $apiKey = env('BROWSERLESS_API_KEY');
        
        \Log::info('User Payslip PDF Generation', [
            'url' => $browserlessUrl,
            'has_api_key' => !empty($apiKey)
        ]);
        
        try {
            $response = \Http::timeout(60)->withOptions([
                'verify' => true,
            ])->post($browserlessUrl . '/pdf?token=' . $apiKey, [
                'html' => $html,
                'options' => [
                    'format' => 'A4',
                    'margin' => [
                        'top' => '10mm',
                        'right' => '10mm',
                        'bottom' => '10mm',
                        'left' => '10mm'
                    ],
                    'printBackground' => true
                ]
            ]);
            
            if ($response->successful()) {
                $monthName = Carbon::create()->month($payslip->month)->format('F');
                $filename = 'payslip_' . $monthName . '_' . $payslip->year . '.pdf';
                
                return response($response->body())
                    ->header('Content-Type', 'application/pdf')
                    ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
            } else {
                \Log::error('Browserless.io API failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return back()->with('error', 'Failed to generate PDF. Please try again.');
            }
        } catch (\Exception $e) {
            \Log::error('Browserless.io error', ['message' => $e->getMessage()]);
            return back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }
}