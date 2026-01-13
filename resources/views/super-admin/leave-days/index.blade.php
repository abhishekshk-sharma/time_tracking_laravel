@extends('super-admin.layouts.app')

@section('content')
<div class="card">
    <div class="card-header">
        <h2 class="card-title">
            <i class="fas fa-calendar-alt"></i>
            Annual Leave Days Configuration
        </h2>
    </div>
    
    <div class="card-body">
        <form action="{{ route('super-admin.leave-days.update') }}" method="POST">
            @csrf
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="casual_leave">Casual Leave Days</label>
                        <input type="number" 
                               class="form-control @error('casual_leave') is-invalid @enderror" 
                               id="casual_leave" 
                               name="casual_leave" 
                               value="{{ old('casual_leave', $currentSettings['casual_leave'] ?? 0) }}" 
                               min="0" 
                               max="365" 
                               required>
                        @error('casual_leave')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Number of casual leave days per year</small>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="sick_leave">Sick Leave Days</label>
                        <input type="number" 
                               class="form-control @error('sick_leave') is-invalid @enderror" 
                               id="sick_leave" 
                               name="sick_leave" 
                               value="{{ old('sick_leave', $currentSettings['sick_leave'] ?? 0) }}" 
                               min="0" 
                               max="365" 
                               required>
                        @error('sick_leave')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Number of sick leave days per year</small>
                    </div>
                </div>
            </div>
            
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <strong>Note:</strong> Updating these values will apply the new leave allocation to all employees in the system.
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Update Leave Days
                </button>
            </div>
        </form>
    </div>
</div>
@endsection