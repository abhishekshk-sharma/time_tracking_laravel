<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Salary;
use App\Services\SalaryCalculationService;
use Illuminate\Http\Request;
use Spatie\Browsershot\Browsershot;
use Illuminate\Support\Facades\Response;
use Carbon\Carbon;

class SalarySlipController extends Controller
{
    protected $salaryCalculationService;
    
    public function __construct(SalaryCalculationService $salaryCalculationService)
    {
        $this->salaryCalculationService = $salaryCalculationService;
    }
    
    public function generate(Request $request)
    {
        // Increase execution time for PDF generation
        set_time_limit(120);
        
        $empId = $request->input('emp_id');
        
        if (!$empId) {
            return back()->with('error', 'Please select an employee for salary slip generation');
        }
        
        $employee = Employee::with('department')->where('emp_id', $empId)->first();
        if (!$employee) {
            return back()->with('error', 'Employee not found');
        }
        
        // Use form data instead of database calculations
        $calculatedBasicSalary = $request->input('basic_salary', 0);
        $hra = $request->input('hra', 0);
        $conveyance = $request->input('conveyance', 0);
        $pt = $request->input('pt', 0);
        $pf = $request->input('pf', 0);
        $grossEarnings = $request->input('gross_earnings', 0);
        $totalDeductions = $request->input('total_deductions', 0);
        $netSalary = $request->input('net_salary', 0);
        
        // Get attendance summary data from form
        $totalWorkingDays = $request->input('total_working_days', 0);
        $payableDays = $request->input('payable_days', 0);
        $perDayRate = $request->input('per_day_rate', 0);
        $originalBasic = $request->input('original_basic', 0);
        
        // Get attendance breakdown data from form
        $holidays = $request->input('holidays', 0);
        $sickLeave = $request->input('sick_leave', 0);
        $casualLeave = $request->input('casual_leave', 0);
        $halfDays = $request->input('half_days', 0);
        $weekOff = $request->input('week_off', 0);
        $absentDays = $request->input('absent_days', 0);
        $shortAttendance = $request->input('short_attendance', 0);
        
        $monthName = $request->input('month_year', date('F Y'));
        $amountInWords = $this->convertToWords($netSalary);
        
        // Create salary object with form data
        $salaryData = (object) [
            'basic_salary' => $calculatedBasicSalary,
            'hra' => $hra,
            'conveyance_allowance' => $conveyance,
            'pt' => $pt,
            'pf' => $pf
        ];
        
        // Update employee data with form values
        $employee->username = $request->input('employee_name', $employee->username);
        $employee->designation = $request->input('designation', $employee->designation);
        $employeeDepartment = $request->input('department', $employee->department->name ?? 'IT');
        
        $data = [
            'employee' => $employee,
            'salary' => $salaryData,
            'calculatedBasicSalary' => $calculatedBasicSalary,
            'month' => $monthName,
            'grossEarnings' => $grossEarnings,
            'totalDeductions' => $totalDeductions,
            'netSalary' => $netSalary,
            'amountInWords' => $amountInWords,
            'employeeDepartment' => $employeeDepartment,
            'totalWorkingDays' => $totalWorkingDays,
            'payableDays' => $payableDays,
            'perDayRate' => $perDayRate,
            'originalBasic' => $originalBasic,
            'holidays' => $holidays,
            'sickLeave' => $sickLeave,
            'casualLeave' => $casualLeave,
            'halfDays' => $halfDays,
            'weekOff' => $weekOff,
            'absentDays' => $absentDays,
            'shortAttendance' => $shortAttendance
        ];
        
        $html = view('super-admin.reports.salary-slip-pdf', $data)->render();
        
        $filename = 'salary_slip_' . $employee->emp_id . '_' . date('Y_m') . '.pdf';
        
        $pdf = Browsershot::html($html)
            ->format('A4')
            ->margins(10, 10, 10, 10)
            ->timeout(60)
            ->setOption('args', [
                '--no-sandbox',
                '--disable-setuid-sandbox',
                '--disable-dev-shm-usage',
                '--disable-gpu',
                '--no-first-run',
                '--disable-extensions',
                '--disable-plugins'
            ])
            ->waitUntilNetworkIdle()
            ->pdf();
        
        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
    
    private function convertToWords($number)
    {
        $number = (int) $number;
        
        if ($number == 0) return 'Zero Rupees Only';
        
        $ones = array('', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen');
        $tens = array('', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety');
        
        $result = '';
        
        if ($number >= 10000000) {
            $crores = intval($number / 10000000);
            $result .= $this->convertHundreds($crores, $ones, $tens) . ' Crore ';
            $number %= 10000000;
        }
        
        if ($number >= 100000) {
            $lakhs = intval($number / 100000);
            $result .= $this->convertHundreds($lakhs, $ones, $tens) . ' Lakh ';
            $number %= 100000;
        }
        
        if ($number >= 1000) {
            $thousands = intval($number / 1000);
            $result .= $this->convertHundreds($thousands, $ones, $tens) . ' Thousand ';
            $number %= 1000;
        }
        
        if ($number >= 100) {
            $hundreds = intval($number / 100);
            $result .= $ones[$hundreds] . ' Hundred ';
            $number %= 100;
        }
        
        if ($number >= 20) {
            $result .= $tens[intval($number / 10)];
            if ($number % 10 > 0) {
                $result .= ' ' . $ones[$number % 10];
            }
        } elseif ($number > 0) {
            $result .= $ones[$number];
        }
        
        return trim($result) . ' Rupees Only';
    }
    
    private function convertHundreds($number, $ones, $tens)
    {
        $result = '';
        
        if ($number >= 100) {
            $result .= $ones[intval($number / 100)] . ' Hundred ';
            $number %= 100;
        }
        
        if ($number >= 20) {
            $result .= $tens[intval($number / 10)];
            if ($number % 10 > 0) {
                $result .= ' ' . $ones[$number % 10];
            }
        } elseif ($number > 0) {
            $result .= $ones[$number];
        }
        
        return trim($result);
    }
}