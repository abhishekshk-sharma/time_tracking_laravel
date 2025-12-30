@extends('layouts.app')

@section('title', 'Welcome')

@section('content')
<div class="container">
    <div class="welcome-content">
        <h1>Welcome to TimeTrack System</h1>
        <p>Redirecting to your dashboard...</p>
    </div>
</div>

<script>
$(document).ready(function() {
    @auth
        @if(Auth::user()->role === 'admin')
            window.location.href = "{{ route('admin.dashboard') }}";
        @else
            window.location.href = "{{ route('dashboard') }}";
        @endif
    @else
        window.location.href = "{{ route('login') }}";
    @endauth
});
</script>

<style>
.welcome-content {
    text-align: center;
    margin-top: 100px;
    color: white;
}

.welcome-content h1 {
    font-size: 2.5rem;
    margin-bottom: 20px;
}

.welcome-content p {
    font-size: 1.2rem;
    opacity: 0.8;
}
</style>
@endsection