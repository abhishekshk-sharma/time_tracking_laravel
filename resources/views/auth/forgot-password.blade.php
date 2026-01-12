@extends('layouts.app')

@section('title', 'Forgot Password')

@section('content')
<div class="login-container">
    <div class="login-card">
        <div class="logo">
            <img src="{{ asset('images/logo.png') }}" alt="" width="80" height="50">
            <h1 style="margin-left: 10px;">ST ZK DM</h1>
        </div>
        
        <div class="login-header">
            <h2>Forgot Password</h2>
            <p>Enter your email address and we'll send you a password reset link</p>
        </div>
        
        @if (session('status'))
            <div class="notification success" style="display: block;">
                <i class="fas fa-check-circle"></i> {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="notification error" style="display: block;">
                <i class="fas fa-exclamation-circle"></i> {{ $errors->first() }}
            </div>
        @endif
        
        <form action="{{ route('password.email') }}" method="POST">
            @csrf
            <div class="input-group">
                <label for="email">Email Address</label>
                <div class="input-with-icon">
                    <i class="fas fa-envelope"></i>
                    <input type="email" id="email" name="email" class="input-field" 
                           placeholder="Enter your email address" value="{{ old('email') }}" required autofocus>
                </div>
            </div>
            
            <button type="submit" class="login-button">
                Send Reset Link
            </button>
            
            <div style="text-align: center; margin-top: 20px;">
                <a href="{{ route('login') }}" style="color: var(--secondary); text-decoration: none;">
                    <i class="fas fa-arrow-left"></i> Back to Login
                </a>
            </div>
        </form>
    </div>
</div>

<style>
    :root {
        --primary: #667eea;
        --secondary: #764ba2;
        --accent: #e74c3c;
        --light: #ecf0f1;
        --dark: #2c3e50;
        --success: #2ecc71;
        --warning: #f39c12;
        --danger: #e74c3c;
        --gray: #95a5a6;
    }
    
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    
    body {
        background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #333;
        line-height: 1.6;
    }
    
    .login-container {
        width: 100%;
        max-width: 400px;
        padding: 20px;
    }
    
    .login-card {
        background: white;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        text-align: center;
    }
    
    .logo {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        margin-bottom: 20px;
    }
    
    .logo h1 {
        font-weight: 700;
        font-size: 28px;
        background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    
    .login-header {
        margin-bottom: 25px;
    }
    
    .login-header h2 {
        font-size: 22px;
        color: var(--dark);
        margin-bottom: 8px;
    }
    
    .login-header p {
        color: var(--gray);
        font-size: 15px;
    }
    
    .input-group {
        margin-bottom: 20px;
        text-align: left;
    }
    
    .input-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: var(--dark);
        font-size: 14px;
    }
    
    .input-field {
        width: 100%;
        padding: 14px 15px;
        border: 2px solid #ddd;
        border-radius: 8px;
        font-size: 16px;
        transition: all 0.3s ease;
    }
    
    .input-field:focus {
        border-color: var(--secondary);
        outline: none;
        box-shadow: 0 0 0 3px rgba(118, 75, 162, 0.2);
    }
    
    .input-with-icon {
        position: relative;
    }
    
    .input-with-icon i {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--gray);
    }
    
    .input-with-icon .input-field {
        padding-left: 45px;
    }
    
    .login-button {
        width: 100%;
        padding: 14px;
        background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-bottom: 20px;
    }
    
    .login-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }
    
    .notification {
        padding: 12px 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        display: none;
    }
    
    .error {
        background-color: #ffebee;
        color: #e74c3c;
        border: 1px solid #ffcdd2;
    }
    
    .success {
        background-color: #e8f5e9;
        color: #2ecc71;
        border: 1px solid #c8e6c9;
    }
</style>
@endsection