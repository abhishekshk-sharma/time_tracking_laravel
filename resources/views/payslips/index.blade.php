@extends('layouts.user')

@section('page-title', 'My Payslips')
@section('page-subtitle', 'View and download your salary slips')

@push('page-styles')
<style>
    .payslips-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }
    
    .payslip-card {
        background: white;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        border: 1px solid #e5e7eb;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    
    .payslip-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        border-color: var(--secondary);
    }
    
    .payslip-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 16px;
    }
    
    .payslip-icon {
        width: 48px;
        height: 48px;
        background: linear-gradient(135deg, var(--secondary), var(--primary));
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 20px;
    }
    
    .payslip-info h3 {
        margin: 0;
        font-size: 18px;
        font-weight: 600;
        color: var(--dark);
    }
    
    .payslip-info p {
        margin: 4px 0 0;
        font-size: 14px;
        color: var(--gray);
    }
    
    .payslip-details {
        margin-bottom: 20px;
    }
    
    .detail-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
        font-size: 14px;
    }
    
    .detail-label {
        color: var(--gray);
    }
    
    .detail-value {
        font-weight: 600;
        color: var(--dark);
    }
    
    .payslip-actions {
        display: flex;
        gap: 10px;
    }
    
    .btn-download {
        flex: 1;
        background: linear-gradient(135deg, var(--secondary), var(--primary));
        color: white;
        border: none;
        padding: 10px 16px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        text-align: center;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    
    .btn-download:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(59, 130, 246, 0.3);
        color: white;
        text-decoration: none;
    }
    
    .no-payslips {
        text-align: center;
        padding: 60px 20px;
        color: var(--gray);
    }
    
    .no-payslips i {
        font-size: 64px;
        margin-bottom: 20px;
        opacity: 0.5;
    }
    
    .year-section {
        margin-bottom: 40px;
    }
    
    .year-header {
        font-size: 24px;
        font-weight: 600;
        color: var(--dark);
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid var(--secondary);
        display: inline-block;
    }
    
    .year-navigation {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 20px;
        margin-bottom: 30px;
        padding: 20px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        border: 1px solid #e5e7eb;
    }
    
    .year-nav-btn {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--secondary), var(--primary));
        color: white;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        transition: all 0.3s ease;
        text-decoration: none;
    }
    
    .year-nav-btn:hover {
        transform: scale(1.1);
        box-shadow: 0 4px 8px rgba(59, 130, 246, 0.3);
        color: white;
        text-decoration: none;
    }
    
    .year-nav-btn:disabled {
        background: #e5e7eb;
        color: #9ca3af;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }
    
    .current-year {
        font-size: 28px;
        font-weight: 700;
        color: var(--dark);
        min-width: 80px;
        text-align: center;
    }
    
    @media (max-width: 768px) {
        .payslips-grid {
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 15px;
        }
        
        .payslip-card {
            padding: 20px;
        }
        
        .year-navigation {
            padding: 15px;
            gap: 15px;
        }
        
        .year-nav-btn {
            width: 40px;
            height: 40px;
            font-size: 16px;
        }
        
        .current-year {
            font-size: 24px;
            min-width: 70px;
        }
    }
    
    @media (max-width: 480px) {
        .payslips-grid {
            grid-template-columns: 1fr;
            gap: 12px;
        }
        
        .payslip-card {
            padding: 16px;
        }
        
        .payslip-header {
            gap: 10px;
        }
        
        .payslip-icon {
            width: 40px;
            height: 40px;
            font-size: 18px;
        }
        
        .payslip-info h3 {
            font-size: 16px;
        }
        
        .current-year {
            font-size: 20px;
            min-width: 60px;
        }
        
        .year-navigation {
            padding: 12px;
            gap: 12px;
        }
        
        .year-nav-btn {
            width: 36px;
            height: 36px;
            font-size: 14px;
        }
    }
    
    @media (max-width: 400px) {
        .payslips-grid {
            margin: 10px -10px;
        }
        
        .payslip-card {
            margin: 0 10px;
            padding: 14px;
        }
        
        .payslip-header {
            flex-direction: column;
            text-align: center;
            gap: 8px;
        }
        
        .payslip-icon {
            width: 36px;
            height: 36px;
            font-size: 16px;
        }
        
        .payslip-info h3 {
            font-size: 15px;
        }
        
        .detail-row {
            font-size: 13px;
        }
        
        .btn-download {
            padding: 8px 12px;
            font-size: 13px;
        }
    }
</style>
@endpush

@section('page-content')
<div class="card">
    <div class="card-header">
        <h2 class="card-title">
            <i class="fas fa-file-invoice-dollar"></i>
            My Payslips
        </h2>
    </div>

    <div class="card-body">
        @if(count($availableYears) > 0)
            <!-- Year Navigation -->
            <div class="year-navigation">
                @php
                    $currentIndex = array_search($selectedYear, $availableYears);
                    $prevYear = $currentIndex < count($availableYears) - 1 ? $availableYears[$currentIndex + 1] : null;
                    $nextYear = $currentIndex > 0 ? $availableYears[$currentIndex - 1] : null;
                @endphp
                
                @if($prevYear)
                    <a href="{{ route('payslips.index', ['year' => $prevYear]) }}" class="year-nav-btn">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                @else
                    <button class="year-nav-btn" disabled>
                        <i class="fas fa-chevron-left"></i>
                    </button>
                @endif
                
                <div class="current-year">{{ $selectedYear }}</div>
                
                @if($nextYear)
                    <a href="{{ route('payslips.index', ['year' => $nextYear]) }}" class="year-nav-btn">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                @else
                    <button class="year-nav-btn" disabled>
                        <i class="fas fa-chevron-right"></i>
                    </button>
                @endif
            </div>
        @endif
        
        @if($payslips->count() > 0)
            <div class="payslips-grid">
                @foreach($payslips as $payslip)
                    <div class="payslip-card">
                        <div class="payslip-header">
                            <div class="payslip-icon">
                                <i class="fas fa-receipt"></i>
                            </div>
                            <div class="payslip-info">
                                <h3>{{ $payslip->month_name }} {{ $payslip->year }}</h3>
                                <p>Salary Slip</p>
                            </div>
                        </div>
                        
                        <div class="payslip-details">
                            <div class="detail-row">
                                <span class="detail-label">Gross Salary:</span>
                                <span class="detail-value">₹{{ number_format($payslip->gross_salary ?? 0, 2) }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Net Salary:</span>
                                <span class="detail-value">₹{{ number_format($payslip->net_salary ?? 0, 2) }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Generated:</span>
                                <span class="detail-value">{{ \Carbon\Carbon::parse($payslip->created_at)->format('M d, Y') }}</span>
                            </div>
                        </div>
                        
                        <div class="payslip-actions">
                            <a href="{{ route('payslips.download', $payslip->id) }}" class="btn-download">
                                <i class="fas fa-download"></i>
                                Download PDF
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @elseif(count($availableYears) > 0)
            <div class="no-payslips">
                <i class="fas fa-file-invoice"></i>
                <h3>No Payslips for {{ $selectedYear }}</h3>
                <p>No payslips found for the selected year. Try navigating to a different year.</p>
            </div>
        @else
            <div class="no-payslips">
                <i class="fas fa-file-invoice"></i>
                <h3>No Payslips Available</h3>
                <p>Your payslips will appear here once they are generated by the admin.</p>
            </div>
        @endif
    </div>
</div>
@endsection