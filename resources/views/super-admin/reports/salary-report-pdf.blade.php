<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payslip - {{ $salaryReport->emp_name }}</title>
    <style>
        @page { margin: 0px; }
        body {
            font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
            margin: 40px;
            color: #202124;
            font-size: 12px;
            line-height: 1.5;
            background-color: #fff;
        }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-uppercase { text-transform: uppercase; }
        .bold { font-weight: bold; }
        .text-gray { color: #5f6368; }
        .text-blue { color: #1a73e8; }
        
        .header-table { width: 100%; border-bottom: 2px solid #1a73e8; padding-bottom: 20px; margin-bottom: 30px; }
        .company-name { font-size: 24px; font-weight: 400; color: #000; letter-spacing: -0.5px; }
        .company-sub { font-size: 10px; color: #5f6368; margin-top: 5px; }
        .payslip-title { font-size: 28px; font-weight: 300; color: #5f6368; text-align: right; }
        
        .section-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #1a73e8;
            margin-bottom: 10px;
            border-bottom: 1px solid #dfe1e5;
            padding-bottom: 5px;
        }
        
        table { width: 100%; border-collapse: collapse; }
        
        .emp-info-table td { padding: 6px 0; vertical-align: top; }
        .emp-label { color: #5f6368; font-size: 10px; text-transform: uppercase; width: 120px; }
        .emp-value { font-weight: 500; color: #202124; }
        
        .attendance-box {
            background-color: #f8f9fa;
            border-radius: 4px;
            padding: 15px;
            margin: 20px 0;
            border: 1px solid #dfe1e5;
        }
        .stat-label { font-size: 9px; text-transform: uppercase; color: #5f6368; display: block; }
        .stat-value { font-size: 14px; font-weight: bold; margin-top: 5px; display: block; }

        .salary-table th {
            text-align: left;
            padding: 10px 10px;
            background-color: #f1f3f4;
            font-size: 10px;
            text-transform: uppercase;
            color: #5f6368;
            border-bottom: 1px solid #dfe1e5;
        }
        .salary-table td {
            padding: 12px 10px;
            border-bottom: 1px solid #f1f3f4;
            font-size: 12px;
        }
        .salary-table .last-row td { border-bottom: 2px solid #dfe1e5; }
        
        .total-box { width: 100%; margin-top: 20px; }
        .net-pay-box {
            background-color: #e8f0fe;
            padding: 20px;
            border-radius: 8px;
            text-align: right;
            border: 1px solid #d2e3fc;
        }
        .net-label { font-size: 12px; color: #1a73e8; text-transform: uppercase; letter-spacing: 1px; }
        .net-amount { font-size: 32px; font-weight: bold; color: #1a73e8; margin: 5px 0; }
        .words { font-size: 11px; font-style: italic; color: #5f6368; }

        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #dfe1e5;
            text-align: center;
            font-size: 10px;
            color: #9aa0a6;
        }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td width="15%">
                <img src="{{ public_path('images/logo.png') }}" alt="Company Logo" style="max-width: 80px; max-height: 60px;">
            </td>
            <td width="45%">
                <div class="company-name">St zk Digital Media co. LLC</div>
                <div class="company-sub">
                    BO: &nbsp; B-1202, Sun west bank, Ashram road, Ahmedabad - 380009<br>
                    https://www.stzkdigitalmedia.com/
                </div>
            </td>
            <td width="40%" style="vertical-align: bottom;" class="text-right">
                <div class="payslip-title">PAYSLIP</div>
                <div class="text-gray" style="margin-top: 5px;">{{ date('F Y', mktime(0, 0, 0, $salaryReport->month, 1, $salaryReport->year)) }}</div>
            </td>
        </tr>
    </table>

    <div class="section-title">Employee Summary</div>
    <table class="emp-info-table" style="margin-bottom: 20px;">
        <tr>
            <td class="emp-label">Employee Name</td>
            <td class="emp-value">{{ strtoupper($salaryReport->emp_name) }}</td>
            <td class="emp-label">Department</td>
            <td class="emp-value">{{ $salaryReport->department }}</td>
        </tr>
        <tr>
            <td class="emp-label">Employee ID</td>
            <td class="emp-value">{{ $salaryReport->emp_id }}</td>
            <td class="emp-label">Designation</td>
            <td class="emp-value">{{ $salaryReport->designation }}</td>
        </tr>
        <tr>
            <td class="emp-label">Region</td>
            <td class="emp-value">{{ $employee->region->name ?? 'N/A' }}</td>
            <td class="emp-label">Admin</td>
            <td class="emp-value">{{ $salaryReport->admin_id ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="emp-label">DOB</td>
            <td class="emp-value">
                @if ($employee->dob !== null)
                    {{ ($employee->dob->format("d M, Y"))??"N/A"  }}
                @else
                    N/A
                @endif
            </td>
            <td class="emp-label">Date of Joining</td>
            <td class="emp-value">{{ $employee->hire_date->format("d M, Y") ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="emp-label">Payment Mode</td>
            <td class="emp-value">{{ ucfirst(str_replace('_', ' ', $salaryReport->payment_mode ?? 'Bank Transfer')) }}</td>
            <td class="emp-label"></td>
            <td class="emp-value"></td>
        </tr>
    </table>

    @if($salaryReport->bank_name || $salaryReport->uan || $salaryReport->pf_no || $salaryReport->esic_no)
    <div class="section-title">Bank & Statutory Details</div>
    <table class="emp-info-table" style="margin-bottom: 20px;">
        @if($salaryReport->bank_name || $salaryReport->bank_account)
        <tr>
            @if($salaryReport->bank_name)
            <td class="emp-label">Bank Name</td>
            <td class="emp-value">{{ $salaryReport->bank_name }}</td>
            @else
            <td class="emp-label"></td>
            <td class="emp-value"></td>
            @endif
            @if($salaryReport->bank_account)
            <td class="emp-label">Account Number</td>
            <td class="emp-value">{{ $salaryReport->bank_account }}</td>
            @else
            <td class="emp-label"></td>
            <td class="emp-value"></td>
            @endif
        </tr>
        @endif
        @if($salaryReport->ifsc_code || $salaryReport->bank_branch)
        <tr>
            @if($salaryReport->ifsc_code)
            <td class="emp-label">IFSC Code</td>
            <td class="emp-value">{{ $salaryReport->ifsc_code }}</td>
            @else
            <td class="emp-label"></td>
            <td class="emp-value"></td>
            @endif
            @if($salaryReport->bank_branch)
            <td class="emp-label">Bank Branch</td>
            <td class="emp-value">{{ $salaryReport->bank_branch }}</td>
            @else
            <td class="emp-label"></td>
            <td class="emp-value"></td>
            @endif
        </tr>
        @endif
        @if($salaryReport->uan || $salaryReport->pf_no)
        <tr>
            @if($salaryReport->uan)
            <td class="emp-label">UAN</td>
            <td class="emp-value">{{ $salaryReport->uan }}</td>
            @else
            <td class="emp-label"></td>
            <td class="emp-value"></td>
            @endif
            @if($salaryReport->pf_no)
            <td class="emp-label">PF Number</td>
            <td class="emp-value">{{ $salaryReport->pf_no }}</td>
            @else
            <td class="emp-label"></td>
            <td class="emp-value"></td>
            @endif
        </tr>
        @endif
        @if($salaryReport->esic_no)
        <tr>
            <td class="emp-label">ESIC Number</td>
            <td class="emp-value">{{ $salaryReport->esic_no }}</td>
            <td class="emp-label"></td>
            <td class="emp-value"></td>
        </tr>
        @endif
    </table>
    @endif

    {{-- <div class="attendance-box">
        <table width="100%" class="emp-info-table" style="margin-bottom: 20px;">
            <tr>
                <td class="text-center">
                    <span class="stat-label">Working Days</span>
                    <span class="stat-value">{{ $salaryReport->total_working_days }}</span>
                </td>
                <td class="text-center" style="border-left: 1px solid #dfe1e5;">
                    <span class="stat-label">Payable Days</span>
                    <span class="stat-value text-blue">{{ $salaryReport->payable_days }}</span>
                </td>
                <td class="text-center" style="border-left: 1px solid #dfe1e5;">
                    <span class="stat-label">Leaves Taken</span>
                    <span class="stat-value">{{ $salaryReport->casual_leave + $salaryReport->sick_leave }}</span>
                </td>
                <td class="text-center" style="border-left: 1px solid #dfe1e5;">
                    <span class="stat-label">Absent Days</span>
                    <span class="stat-value">{{ $salaryReport->absent_days }}</span>
                </td>
            </tr>
        </table>
    </div> --}}

    <table class="salary-table" style="margin-top: 20px;">
        <thead>
            <tr>
                <th width="40%">Earnings</th>
                <th width="15%" class="text-right">Amount</th>
                <th width="5%"></th>
                <th width="25%">Deductions</th>
                <th width="15%" class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                @if ($salaryReport->payable_basic_salary > 0)
                    <td>Basic Salary</td>
                    <td class="text-right">{{ number_format($salaryReport->payable_basic_salary, 2) }}</td>
                @else
                    <td></td>
                    <td></td>
                @endif
                <td></td>

                @if ($salaryReport->pt > 0)
                    <td>Professional Tax</td>
                    <td class="text-right">{{ number_format($salaryReport->pt, 2) }}</td>

                @else
                    <td></td>
                    <td></td>
                @endif
            </tr>
            <tr>
                @if ($salaryReport->hra > 0)
                    <td>House Rent Allowance</td>
                    <td class="text-right">{{ number_format($salaryReport->hra, 2) }}</td>
                @else
                    <td></td>
                    <td></td>
                @endif
                <td></td>
                @if ($salaryReport->pf > 0)
                    <td>Provident Fund</td>
                <td class="text-right">{{ number_format($salaryReport->pf, 2) }}</td>
                @else
                    <td></td>
                    <td></td>
                @endif
            </tr>
            <tr>
                @if ($salaryReport->conveyance_allowance > 0)
                    <td>Conveyance Allowance</td>
                    <td class="text-right">{{ number_format($salaryReport->conveyance_allowance, 2) }}</td>
                @else
                    <td></td>
                    <td></td>
                @endif
                <td></td>
                @if ($salaryReport->tds > 0)
                    <td>TDS</td>
                <td class="text-right">{{ number_format($salaryReport->tds, 2) }}</td>
                @else
                    <td></td>
                    <td></td>
                @endif
            </tr>
            <tr>
                @if ($salaryReport->special_allowance > 0)
                    <td>Special Allowance</td>
                    <td class="text-right">{{ number_format($salaryReport->special_allowance, 2) }}</td>
                @else
                    <td></td>
                    <td></td>
                @endif
                <td></td>
                @if ($salaryReport->healthcare_cess > 0)
                    <td>Healthcare Cess</td>
                <td class="text-right">{{ number_format($salaryReport->healthcare_cess, 2) }}</td>
                @else
                    <td></td>
                    <td></td>
                @endif
            </tr>
            <tr class="last-row">
                <td style="height: 20px;"></td><td></td><td></td><td></td><td></td>
            </tr>
            
            <tr style="background-color: #fff;">
                <td class="bold text-gray">TOTAL EARNINGS</td>
                <td class="bold text-right">&#8377; {{ number_format($salaryReport->gross_salary, 2) }}</td>
                <td></td>
                <td class="bold text-gray">TOTAL DEDUCTIONS</td>
                <td class="bold text-right">&#8377; {{ number_format($salaryReport->total_deductions, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <table width="100%" class="emp-info-table" style="margin-bottom: 20px; margin-top: 20px;">
        <tr>
            <td class="emp-label">Working Days</td>
            <td class="emp-label">{{ $salaryReport->total_working_days }}</td>
        </tr>
        <tr>
            <td class="emp-label">Payable Days</td>
            <td class="emp-label">{{ $salaryReport->payable_days }}</td>
        </tr>
        <tr>
            <td class="emp-label">Leaves Taken</td>
            <td class="emp-label">{{ $salaryReport->casual_leave + $salaryReport->sick_leave }}</td>
        </tr>
        <tr>
            <td class="emp-label">Absent Days</td>
            <td class="emp-label">{{ $salaryReport->absent_days }}</td>
        </tr>
    </table>

    <table width="100%" style="margin-top: 20px;">
        <tr>
            <td width="60%"></td>
            <td width="40%">
                <div class="net-pay-box">
                    <div class="net-label">Net Salary Payable</div>
                    <div class="net-amount">&#8377; {{ number_format($salaryReport->net_salary, 2) }}</div>
                </div>
            </td>
        </tr>
    </table>

    <div class="footer">
        Confidential Information • St zk Digital Media co. LLC • Private & Confidential
    </div>
    <div class="footer" style="margin-top: 20px;">
        <span style="font-size: 10px;   ">Generated by System • No Signature Required</span>
    </div>

</body>
</html>