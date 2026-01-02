<!DOCTYPE html>
<html>
<head>
    <title>Salary Slip - {{ $employee['name'] }}</title>
    <style>
        body {
            font-family: Calibri, Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: white;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11pt;
        }
        .header-table {
            margin-bottom: 10px;
        }
        .company-header {
            background-color: #4472C4;
            color: white;
            font-size: 16pt;
            font-weight: bold;
            text-align: center;
            padding: 10px;
            border: 1px solid #000;
        }
        .slip-title {
            background-color: #D9E1F2;
            font-size: 14pt;
            font-weight: bold;
            text-align: center;
            padding: 8px;
            border: 1px solid #000;
        }
        .info-cell {
            border: 1px solid #000;
            padding: 5px 8px;
            vertical-align: top;
        }
        .label-cell {
            background-color: #F2F2F2;
            font-weight: bold;
            width: 150px;
        }
        .value-cell {
            background-color: white;
        }
        .earnings-header {
            background-color: #70AD47;
            color: white;
            font-weight: bold;
            text-align: center;
            padding: 8px;
            border: 1px solid #000;
        }
        .deductions-header {
            background-color: #E74C3C;
            color: white;
            font-weight: bold;
            text-align: center;
            padding: 8px;
            border: 1px solid #000;
        }
        .earnings-cell {
            background-color: #E2EFDA;
            border: 1px solid #000;
            padding: 5px 8px;
        }
        .deductions-cell {
            background-color: #FADBD8;
            border: 1px solid #000;
            padding: 5px 8px;
        }
        .amount-cell {
            text-align: right;
            font-weight: bold;
        }
        .total-row {
            background-color: #BDD7EE;
            font-weight: bold;
        }
        .net-salary-row {
            background-color: #FFD966;
            font-weight: bold;
            font-size: 12pt;
        }
        .footer-cell {
            background-color: #F8F9FA;
            border: 1px solid #000;
            padding: 5px 8px;
            text-align: center;
            font-size: 9pt;
        }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td class="company-header" colspan="4">{{ $company_name }}</td>
        </tr>
        <tr>
            <td class="slip-title" colspan="4">EMPLOYEE SALARY SLIP - {{ $month_year }}</td>
        </tr>
    </table>

    <table style="margin-bottom: 15px;">
        <tr>
            <td class="info-cell label-cell">Employee ID</td>
            <td class="info-cell value-cell">{{ $employee['emp_id'] }}</td>
            <td class="info-cell label-cell">Employee Name</td>
            <td class="info-cell value-cell">{{ $employee['name'] }}</td>
        </tr>
        <tr>
            <td class="info-cell label-cell">Department</td>
            <td class="info-cell value-cell">{{ $employee['department'] }}</td>
            <td class="info-cell label-cell">Designation</td>
            <td class="info-cell value-cell">{{ $employee['position'] }}</td>
        </tr>
        <tr>
            <td class="info-cell label-cell">Email</td>
            <td class="info-cell value-cell">{{ $employee['email'] }}</td>
            <td class="info-cell label-cell">Phone</td>
            <td class="info-cell value-cell">{{ $employee['phone'] }}</td>
        </tr>
    </table>

    <table>
        <tr>
            <td class="earnings-header" style="width: 40%;">EARNINGS</td>
            <td class="earnings-header" style="width: 15%;">AMOUNT (₹)</td>
            <td class="deductions-header" style="width: 30%;">DEDUCTIONS</td>
            <td class="deductions-header" style="width: 15%;">AMOUNT (₹)</td>
        </tr>
        <tr>
            <td class="earnings-cell">Basic Salary</td>
            <td class="earnings-cell amount-cell">{{ number_format($salary['basic_salary'], 2) }}</td>
            <td class="deductions-cell">Provident Fund (PF)</td>
            <td class="deductions-cell amount-cell">{{ number_format($salary['pf'], 2) }}</td>
        </tr>
        <tr>
            <td class="earnings-cell">House Rent Allowance (HRA)</td>
            <td class="earnings-cell amount-cell">{{ number_format($salary['hra'], 2) }}</td>
            <td class="deductions-cell">Professional Tax (PT)</td>
            <td class="deductions-cell amount-cell">{{ number_format($salary['pt'], 2) }}</td>
        </tr>
        <tr>
            <td class="earnings-cell">Conveyance Allowance</td>
            <td class="earnings-cell amount-cell">{{ number_format($salary['conveyance_allowance'], 2) }}</td>
            <td class="deductions-cell">Other Deductions</td>
            <td class="deductions-cell amount-cell">0.00</td>
        </tr>
        <tr>
            <td class="earnings-cell">Special Allowance</td>
            <td class="earnings-cell amount-cell">0.00</td>
            <td class="deductions-cell">Income Tax</td>
            <td class="deductions-cell amount-cell">0.00</td>
        </tr>
        <tr class="total-row">
            <td class="info-cell">TOTAL EARNINGS</td>
            <td class="info-cell amount-cell">{{ number_format($salary['basic_salary'] + $salary['hra'] + $salary['conveyance_allowance'], 2) }}</td>
            <td class="info-cell">TOTAL DEDUCTIONS</td>
            <td class="info-cell amount-cell">{{ number_format($salary['pf'] + $salary['pt'], 2) }}</td>
        </tr>
        <tr class="net-salary-row">
            <td class="info-cell" colspan="3">NET SALARY PAYABLE</td>
            <td class="info-cell amount-cell">{{ number_format($salary['gross_salary'], 2) }}</td>
        </tr>
    </table>

    <table style="margin-top: 15px;">
        <tr>
            <td class="footer-cell" colspan="2">Effective From: {{ $salary['effective_from'] }}</td>
            <td class="footer-cell" colspan="2">Generated On: {{ date('d-M-Y H:i:s') }}</td>
        </tr>
        <tr>
            <td class="footer-cell" colspan="4">This is a computer generated salary slip and does not require signature</td>
        </tr>
    </table>

    @if(isset($format) && $format === 'xlsx')
    <script>
        window.onload = function() {
            // Create structured CSV with better layout and visual separators
            const csvContent = `,,{{ $company_name }},,\n` +
                `,,EMPLOYEE SALARY SLIP - {{ $month_year }},,\n` +
                `,,,,\n` +
                `Employee ID,{{ $employee['emp_id'] }},,Employee Name,{{ $employee['name'] }}\n` +
                `Department,{{ $employee['department'] }},,Designation,{{ $employee['position'] }}\n` +
                `Email,{{ $employee['email'] }},,Phone,{{ $employee['phone'] }}\n` +
                `,,,,\n` +
                `==========,==========,==========,==========,==========\n` +
                `EARNINGS,AMOUNT (₹),|,DEDUCTIONS,AMOUNT (₹)\n` +
                `==========,==========,==========,==========,==========\n` +
                `Basic Salary,{{ $salary['basic_salary'] }},|,Provident Fund (PF),{{ $salary['pf'] }}\n` +
                `House Rent Allowance (HRA),{{ $salary['hra'] }},|,Professional Tax (PT),{{ $salary['pt'] }}\n` +
                `Conveyance Allowance,{{ $salary['conveyance_allowance'] }},|,Other Deductions,0\n` +
                `----------,----------,----------,----------,----------\n` +
                `TOTAL EARNINGS,{{ $salary['basic_salary'] + $salary['hra'] + $salary['conveyance_allowance'] }},|,TOTAL DEDUCTIONS,{{ $salary['pf'] + $salary['pt'] }}\n` +
                `==========,==========,==========,==========,==========\n` +
                `NET SALARY PAYABLE,{{ $salary['gross_salary'] }},,₹ {{ number_format($salary['gross_salary'], 2) }},\n` +
                `==========,==========,==========,==========,==========\n` +
                `,,,,\n` +
                `Effective From: {{ $salary['effective_from'] }},,Generated On: {{ date('d-M-Y H:i:s') }},,\n` +
                `This is a computer generated salary slip,and does not require signature,,,`;
            
            const blob = new Blob([csvContent], { 
                type: 'application/vnd.ms-excel' 
            });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'Salary_Slip_{{ $employee["emp_id"] }}_{{ date("M_Y") }}.csv';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
        }
    </script>
    @else
    <script>
        window.onload = function() {
            window.print();
        }
    </script>
    @endif
</body>
</html>