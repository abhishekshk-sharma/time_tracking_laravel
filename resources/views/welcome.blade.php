<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Laravel</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
    </head>
    <body class="antialiased">
        <div class="relative sm:flex sm:justify-center sm:items-center min-h-screen bg-dots-darker bg-center bg-gray-100 selection:bg-red-500 selection:text-white">
            <div class="max-w-7xl mx-auto p-6 lg:p-8">
                <div class="flex justify-center">
                    <h1>Time Tracking Laravel Application</h1>
                </div>
            </div>
        </div>
    </body>
</html>@extends('layouts.app')

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