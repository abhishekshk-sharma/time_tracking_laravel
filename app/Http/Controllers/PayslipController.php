<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SalaryReport;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Spatie\Browsershot\Browsershot;

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
            
        // Get payslips for selected year only
        $payslips = SalaryReport::where('emp_id', $empId)
            ->where('year', $selectedYear)
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
            ->firstOrFail();
            
        $employee = Employee::where('emp_id', $payslip->emp_id)->first();
        
        $html = view('payslips.pdf', ['salaryReport' => $payslip, 'employee' => $employee])->render();
        
        $pdf = Browsershot::html($html)
            ->format('A4')
            ->margins(10, 10, 10, 10)
            ->showBackground()
            ->waitUntilNetworkIdle()
            ->pdf();
            
        $monthName = Carbon::create()->month($payslip->month)->format('F');
        $filename = 'payslip_' . $monthName . '_' . $payslip->year . '.pdf';
        
        return response($pdf)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}