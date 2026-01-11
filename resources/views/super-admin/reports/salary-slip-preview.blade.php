@extends('super-admin.layouts.app')

@section('title', 'Salary Slip Preview')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <button onclick="window.history.back()" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back
            </button>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-5">
            <!-- Header -->
            <div class="border-bottom border-primary pb-3 mb-4">
                <div class="row align-items-end">
                    <div class="col-8">
                        <h4 class="mb-1" style="color: #000; font-weight: 400;">St zk Digital Media co. LLC</h4>
                        <small class="text-muted">
                            BO: B-1202, Sun west bank, Ashram road, Ahmedabad - 380009<br>
                            https://www.stzkdigitalmedia.com/
                        </small>
                    </div>
                    <div class="col-4 text-end">
                        <h3 class="text-muted mb-1" style="font-weight: 300;">PAYSLIP</h3>
                        <div class="text-muted">{{ date('F Y', mktime(0, 0, 0, $salaryReport->month, 1, $salaryReport->year)) }}</div>
                    </div>
                </div>
            </div>
            
            <!-- Employee Summary -->
            <div class="mb-3">
                <h6 class="text-primary text-uppercase mb-2" style="font-size: 11px; font-weight: 700; letter-spacing: 0.5px; border-bottom: 1px solid #dfe1e5; padding-bottom: 5px;">Employee Summary</h6>
                <div class="row">
                    <div class="col-3">
                        <small class="text-muted text-uppercase">Employee Name</small><br>
                        <strong>{{ strtoupper($salaryReport->emp_name) }}</strong>
                    </div>
                    <div class="col-3">
                        <small class="text-muted text-uppercase">Employee ID</small><br>
                        <strong>{{ $salaryReport->emp_id }}</strong>
                    </div>
                    <div class="col-3">
                        <small class="text-muted text-uppercase">Department</small><br>
                        <strong>{{ $salaryReport->department }}</strong>
                    </div>
                    <div class="col-3">
                        <small class="text-muted text-uppercase">Designation</small><br>
                        <strong>{{ $salaryReport->designation }}</strong>
                    </div>
                    <div class="col-3">
                        <small class="text-muted text-uppercase">Region</small><br>
                        <strong>{{ $employee->region->name??"N/A" }}</strong>
                    </div>
                    <div class="col-3">
                        <small class="text-muted text-uppercase">Admin</small><br>
                        <strong>{{ $salaryReport->admin_id }}</strong>
                    </div>
                    <div class="col-3">
                        <small class="text-muted text-uppercase">DOB</small><br>
                        <strong>{{ ($employee->dob->format("d M, Y"))??"N/A"  }}</strong>
                    </div>
                    <div class="col-3">
                        <small class="text-muted text-uppercase">Date Of joining</small><br>
                        <strong>{{ $employee->hire_date->format("d M,Y") }}</strong>
                    </div>
                </div>
            </div>
            
            <!-- Bank & Statutory Details -->
            @if($salaryReport->bank_name || $salaryReport->uan || $salaryReport->pf_no || $salaryReport->esic_no)
            <div class="mb-3">
                <h6 class="text-primary text-uppercase mb-2" style="font-size: 11px; font-weight: 700; letter-spacing: 0.5px; border-bottom: 1px solid #dfe1e5; padding-bottom: 5px;">Bank & Statutory Details</h6>
                <div class="row">
                    @if($salaryReport->bank_name)
                    <div class="col-3">
                        <small class="text-muted text-uppercase">Bank Name</small><br>
                        <strong>{{ $salaryReport->bank_name }}</strong>
                    </div>
                    @endif
                    @if($salaryReport->bank_account)
                    <div class="col-3">
                        <small class="text-muted text-uppercase">Account Number</small><br>
                        <strong>{{ $salaryReport->bank_account }}</strong>
                    </div>
                    @endif
                    @if($salaryReport->ifsc_code)
                    <div class="col-3">
                        <small class="text-muted text-uppercase">IFSC Code</small><br>
                        <strong>{{ $salaryReport->ifsc_code }}</strong>
                    </div>
                    @endif
                    @if($salaryReport->uan)
                    <div class="col-3">
                        <small class="text-muted text-uppercase">UAN</small><br>
                        <strong>{{ $salaryReport->uan }}</strong>
                    </div>
                    @endif
                    @if($salaryReport->pf_no)
                    <div class="col-3">
                        <small class="text-muted text-uppercase">PF Number</small><br>
                        <strong>{{ $salaryReport->pf_no }}</strong>
                    </div>
                    @endif
                    @if($salaryReport->esic_no)
                    <div class="col-3">
                        <small class="text-muted text-uppercase">ESIC Number</small><br>
                        <strong>{{ $salaryReport->esic_no }}</strong>
                    </div>
                    @endif
                </div>
            </div>
            @endif
            
            <!-- Attendance Box -->
            {{-- <div class="bg-light border rounded p-3 mb-4">
                <div class="row text-center">
                    <div class="col-3">
                        <small class="text-muted text-uppercase d-block">Working Days</small>
                        <strong class="fs-5">{{ $salaryReport->total_working_days }}</strong>
                    </div>
                    <div class="col-3 border-start">
                        <small class="text-muted text-uppercase d-block">Payable Days</small>
                        <strong class="fs-5 text-primary">{{ $salaryReport->payable_days }}</strong>
                    </div>
                    <div class="col-3 border-start">
                        <small class="text-muted text-uppercase d-block">Leaves Taken</small>
                        <strong class="fs-5">{{ $salaryReport->casual_leave + $salaryReport->sick_leave }}</strong>
                    </div>
                    <div class="col-3 border-start">
                        <small class="text-muted text-uppercase d-block">Absent Days</small>
                        <strong class="fs-5">{{ $salaryReport->absent_days }}</strong>
                    </div>
                </div>
            </div> --}}
            
            <!-- Salary Table -->
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 40%;">Earnings</th>
                            <th class="text-end" style="width: 15%;">Amount</th>
                            <th style="width: 5%;"></th>
                            <th style="width: 25%;">Deductions</th>
                            <th class="text-end" style="width: 15%;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Basic Salary</td>
                            <td class="text-end">{{ number_format($salaryReport->payable_basic_salary, 2) }}</td>
                            <td></td>
                            <td>Professional Tax</td>
                            <td class="text-end">{{ number_format($salaryReport->pt, 2) }}</td>
                        </tr>
                        <tr>
                            <td>House Rent Allowance</td>
                            <td class="text-end">{{ number_format($salaryReport->hra, 2) }}</td>
                            <td></td>
                            <td>Provident Fund</td>
                            <td class="text-end">{{ number_format($salaryReport->pf, 2) }}</td>
                        </tr>
                        <tr>
                            <td>Conveyance Allowance</td>
                            <td class="text-end">{{ number_format($salaryReport->conveyance_allowance, 2) }}</td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr class="table-secondary">
                            <td><strong>TOTAL EARNINGS</strong></td>
                            <td class="text-end"><strong>₹ {{ number_format($salaryReport->gross_salary, 2) }}</strong></td>
                            <td></td>
                            <td><strong>TOTAL DEDUCTIONS</strong></td>
                            <td class="text-end"><strong>₹ {{ number_format($salaryReport->total_deductions, 2) }}</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Attendance Box -->
            
            <div class="mb-3">
                <div class="col-3">
                    <small class="text-muted text-uppercase ">Working Days</small>
                    <strong>{{ $salaryReport->total_working_days }}</strong>
                </div>
                <div class="col-3 ">
                    <small class="text-muted text-uppercase ">Payable Days</small>
                    <strong >{{ $salaryReport->payable_days }}</strong>
                </div>
                <div class="col-3 ">
                    <small class="text-muted text-uppercase ">Leaves Taken</small>
                    <strong >{{ $salaryReport->casual_leave + $salaryReport->sick_leave }}</strong>
                </div>
                <div class="col-3 ">
                    <small class="text-muted text-uppercase ">Absent Days</small>
                    <strong >{{ $salaryReport->absent_days }}</strong>
                </div>
            </div>
            
            
            <!-- Net Salary -->
            <div class="row mt-4">
                <div class="col-6">
                    
                </div>
                <div class="col-6">
                    <div class="bg-primary bg-opacity-10 border border-primary rounded p-3 text-end">
                        <small class="text-primary text-uppercase">Net Salary Payable</small>
                        <h3 class="text-primary mb-0">₹ {{ number_format($salaryReport->net_salary, 2) }}</h3>
                    </div>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="border-top mt-4 pt-3 text-center">
                <small class="text-muted">Confidential Information • St zk Digital Media co. LLC • Private & Confidential</small>
            </div>
            <div class="border-top mt-4 pt-3 text-center">
                <small class="text-muted">Generated by System • No Signature Required</small>
            </div>
        </div>
    </div>
</div>
@endsection