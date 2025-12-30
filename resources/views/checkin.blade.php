


@extends('layouts.app')

@section('title', 'TimeTrack - Check In')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
   
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
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
            background: linear-gradient(135deg, #4b6cb7 0%, #182848 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #333;
            line-height: 1.6;
        }
        
        .login-container {
            margin-top: 100px;
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
        
        .logo i {
            font-size: 32px;
            color: var(--secondary);
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
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
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
        
        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .remember {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .remember input {
            width: 16px;
            height: 16px;
        }
        
        .forgot-password {
            color: var(--secondary);
            text-decoration: none;
            font-weight: 600;
        }
        
        .forgot-password:hover {
            text-decoration: underline;
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
        
        .divider {
            display: flex;
            align-items: center;
            margin: 20px 0;
        }
        
        .divider span {
            flex: 1;
            height: 1px;
            background: #ddd;
        }
        
        .divider p {
            padding: 0 15px;
            color: var(--gray);
            font-size: 14px;
        }
        
        .social-login {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .social-btn {
            flex: 1;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background: white;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .social-btn:hover {
            background: #f9f9f9;
        }
        
        .social-btn i {
            font-size: 18px;
        }
        
        .google-btn {
            color: #DB4437;
        }
        
        .microsoft-btn {
            color: #0078D7;
        }
        
        .signup-link {
            font-size: 14px;
            color: var(--gray);
        }
        
        .signup-link a {
            color: var(--secondary);
            text-decoration: none;
            font-weight: 600;
        }
        
        .signup-link a:hover {
            text-decoration: underline;
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
        
        @media (max-width: 480px) {
            .login-container {
                padding: 15px;
            }
            
            .login-card {
                padding: 20px;
            }
            
            .social-login {
                flex-direction: column;
            }
            
            .remember-forgot {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
        }
    </style>






    <div class="login-container">
        <div class="login-card">
            <div class="logo">
                <img src="includes/logo.png" alt="" width="80" height="50" >
                <h1>ST ZK DM </h1>
            </div>
            
            <div class="login-header">
                <h2>Welcome Back</h2>
                <p>Sign in to your account to continue</p>
            </div>
            
            <div id="error-message" class="notification error">
                <i class="fas fa-exclamation-circle"></i> Invalid username or password
            </div>
            
            <form id="login-form" action="{{ route('login') }}" method="POST">
                @csrf
                <div class="input-group">
                    <label for="username">Username</label>
                    <div class="input-with-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" id="username" name="username" class="input-field" placeholder="Enter your username" required>
                    </div>
                </div>
                
                <div class="input-group">
                    <label for="password">Password</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" class="input-field" placeholder="Enter your password" required>
                        <i class="fas fa-eye-slash" style="position:relative; float:right;transform: translate( -150%,-190%); cursor: pointer;" id="togglePassword"></i>
                    </div>
                </div>
                

                
                <button type="submit" class="login-button">Sign In</button>

                <span>For <a href="{{ route('login') }}">Punch In/Out</a></span><br>
                <span>Forget Password? <a href="{{ route('password.request') }}">Click Here</a>!</span>
            </form>
            

            

        </div>
    </div>
        <script src="{{ asset('js/jQuery.min.js') }}"></script>
         <script src="{{ asset('js/sweetAlert.js') }}"></script>
        

    <script>
    // prevent all kind of functions by user.
        
    document.addEventListener('contextmenu', e => e.preventDefault());
    document.onkeydown = function (e) {
    // F12
    if (e.keyCode === 123) return false;

    // Ctrl+Shift+I, Ctrl+Shift+J, Ctrl+Shift+C
    if (e.ctrlKey && e.shiftKey && ['I', 'J', 'C'].includes(e.key.toUpperCase())) return false;

    // Ctrl+U (View Source)
    if (e.ctrlKey && e.key.toUpperCase() === 'U') return false;
    };
    
    document.addEventListener('selectstart', e => e.preventDefault());
    document.addEventListener('dragstart', e => e.preventDefault());
    document.addEventListener('copy', e => e.preventDefault());

    


    // Toggle password visibility
        $("#togglePassword").click(function(){
            let passwordField = $("#password");
            let type = passwordField.attr("type") === "password" ? "text" : "password";
            passwordField.attr("type", type);
            this.classList.toggle('fa-eye');
        });
        
        
            @if(session('error'))
                Swal.fire({
                    title: "{{ session('error') }}",
                    icon: "error",
                    showConfirmButton: true
                });
            @endif
            
            @if(session('inactive'))
                Swal.fire({
                    text: "You're an inactive user. You can't login!",
                    icon: 'info',
                    showCancelButton: false,
                    confirmButtonText: 'Ok'
                });
            @endif

        
        
        function showError(message) {
            const errorDiv = document.getElementById('error-message');
            errorDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;
            errorDiv.style.display = 'block';
            
            // Hide after 5 seconds
            setTimeout(() => {
                errorDiv.style.display = 'none';
            }, 5000);
        }
        
        function showSuccess(message) {
            const errorDiv = document.getElementById('error-message');
            errorDiv.className = 'notification success';
            errorDiv.innerHTML = `<i class="fas fa-check-circle"></i> ${message}`;
            errorDiv.style.display = 'block';
        }
        
        // Add focus effects to inputs
        const inputs = document.querySelectorAll('.input-field');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('focused');
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.classList.remove('focused');
            });
        });
    </script>
@endsection