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
            <td class="emp-value">{{ $salaryReport->region->name ?? 'N/A' }}</td>
            <td class="emp-label">Admin</td>
            <td class="emp-value">{{ $salaryReport->admin_id ?? 'N/A' }}</td>
        </tr>
    </table>

    <div class="attendance-box">
        <table width="100%">
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
    </div>

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
                <td>Basic Salary</td>
                <td class="text-right">{{ number_format($salaryReport->payable_basic_salary, 2) }}</td>
                <td></td>
                <td>Professional Tax</td>
                <td class="text-right">{{ number_format($salaryReport->pt, 2) }}</td>
            </tr>
            <tr>
                <td>House Rent Allowance</td>
                <td class="text-right">{{ number_format($salaryReport->hra, 2) }}</td>
                <td></td>
                <td>Provident Fund</td>
                <td class="text-right">{{ number_format($salaryReport->pf, 2) }}</td>
            </tr>
            <tr>
                <td>Conveyance Allowance</td>
                <td class="text-right">{{ number_format($salaryReport->conveyance_allowance, 2) }}</td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr class="last-row">
                <td style="height: 20px;"></td><td></td><td></td><td></td><td></td>
            </tr>
            
            <tr style="background-color: #fff;">
                <td class="bold text-gray">TOTAL EARNINGS</td>
                <td class="bold text-right">Rs. {{ number_format($salaryReport->gross_salary, 2) }}</td>
                <td></td>
                <td class="bold text-gray">TOTAL DEDUCTIONS</td>
                <td class="bold text-right">Rs. {{ number_format($salaryReport->total_deductions, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="total-box">
        <table width="100%">
            <tr>
                <td width="60%">
                    <div style="margin-top: 20px;">
                        <span class="text-gray" style="font-size: 10px;">Generated by System • No Signature Required</span>
                    </div>
                </td>
                <td width="40%">
                    <div class="net-pay-box">
                        <div class="net-label">Net Salary Payable</div>
                        <div class="net-amount">Rs. {{ number_format($salaryReport->net_salary, 2) }}</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        Confidential Information • St zk Digital Media co. LLC • Private & Confidential
    </div>

</body>
</html>