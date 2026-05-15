<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TimeTrack - Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-light: #6366f1;
            --secondary: #0ea5e9;
            --white: #ffffff;
            --dark: #0f172a;
            --gray-light: #f8fafc;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--dark);
            overflow: hidden;
            position: relative;
        }

        .bg-shapes {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            z-index: 0; overflow: hidden;
        }

        .shape {
            position: absolute;
            filter: blur(80px);
            opacity: 0.6;
            animation: float 20s infinite ease-in-out alternate;
        }

        .shape-1 {
            width: 400px; height: 400px;
            background: var(--primary);
            top: -100px; left: -100px;
            animation-delay: 0s;
        }

        .shape-2 {
            width: 500px; height: 500px;
            background: var(--secondary);
            bottom: -150px; right: -100px;
            animation-delay: -5s;
        }
        
        .shape-3 {
            width: 300px; height: 300px;
            background: #8b5cf6;
            bottom: 20%; left: 20%;
            animation-delay: -10s;
        }

        @keyframes float {
            0% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(50px, 30px) scale(1.1); }
            100% { transform: translate(-30px, 50px) scale(0.9); }
        }

        .login-container {
            width: 100%; max-width: 420px;
            padding: 2rem; position: relative; z-index: 10;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 2.5rem 2rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            animation: slideUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            opacity: 0; transform: translateY(30px);
        }

        @keyframes slideUp {
            to { opacity: 1; transform: translateY(0); }
        }

        .logo-area {
            text-align: center; margin-bottom: 2.5rem;
        }

        .logo-area img {
            width: 90px; height: auto;
            margin-bottom: 0.75rem;
            filter: drop-shadow(0 4px 6px rgba(0,0,0,0.2));
            background: white;
            border-radius: 12px;
            padding: 10px;
        }

        .logo-area h1 {
            font-size: 1.75rem; font-weight: 800;
            color: var(--white);
            letter-spacing: -0.5px;
            margin-bottom: 0.25rem;
        }
        
        .logo-area p {
            color: rgba(255,255,255,0.7);
            font-size: 0.95rem;
        }

        .form-group {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .form-input {
            width: 100%;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 1.25rem 1rem 0.5rem;
            color: var(--white);
            font-size: 1rem;
            transition: var(--transition);
        }

        .form-input:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.3);
            box-shadow: 0 0 0 4px rgba(255, 255, 255, 0.05);
        }

        .form-label {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.5);
            font-size: 1rem;
            pointer-events: none;
            transition: var(--transition);
        }

        .form-input:focus ~ .form-label,
        .form-input:not(:placeholder-shown) ~ .form-label {
            top: 0.6rem;
            font-size: 0.75rem;
            color: var(--white);
            font-weight: 500;
            opacity: 0.8;
        }

        .password-toggle {
            position: absolute; right: 1rem; top: 50%;
            transform: translateY(-50%);
            background: none; border: none;
            color: rgba(255, 255, 255, 0.5);
            cursor: pointer; padding: 0.25rem;
            transition: var(--transition);
        }

        .password-toggle:hover { color: var(--white); }

        .btn-submit {
            width: 100%;
            padding: 1rem;
            background: var(--white);
            color: var(--dark);
            border: none;
            border-radius: 12px;
            font-size: 1.05rem;
            font-weight: 700;
            cursor: pointer;
            transition: var(--transition);
            margin-top: 0.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(255, 255, 255, 0.2);
            background: var(--gray-light);
        }

        .btn-submit:active { transform: translateY(0); }

        .action-links {
            margin-top: 1.5rem; text-align: center;
        }

        .action-links a {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none; font-size: 0.9rem;
            font-weight: 500; transition: var(--transition);
        }

        .action-links a:hover {
            color: var(--white);
        }

        .alert {
            padding: 1rem; border-radius: 12px; margin-bottom: 1.5rem;
            font-size: 0.9rem; font-weight: 500; backdrop-filter: blur(10px);
            animation: slideIn 0.3s ease-out; display: flex; align-items: center; gap: 0.5rem;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .alert-danger { background: rgba(239, 68, 68, 0.15); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.3); }
        .alert-success { background: rgba(16, 185, 129, 0.15); color: #6ee7b7; border: 1px solid rgba(16, 185, 129, 0.3); }

        @media (max-width: 480px) {
            .login-container { padding: 1.25rem; }
            .login-card { padding: 2rem 1.5rem; border-radius: 20px; }
            .logo-area h1 { font-size: 1.5rem; }
        }
    </style>
</head>
<body>
    <div class="bg-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
    </div>

    <div class="login-container">
        <div class="login-card">
            <div class="logo-area">
                <img src="{{ asset('images/logo.png') }}" alt="TimeTrack Logo">
                <h1>Welcome Back</h1>
                <p>Sign in to your account</p>
            </div>
            
            @if (session('status'))
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> {{ session('status') }}
                </div>
            @endif
            
            @if ($errors->any())
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div>
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                </div>
            @endif
            
            <form action="{{ route('login') }}" method="POST">
                @csrf
                
                <div class="form-group">
                    <input type="text" id="username" name="username" class="form-input" 
                           placeholder=" " required value="{{ old('username') }}">
                    <label for="username" class="form-label">Username</label>
                </div>
                
                <div class="form-group">
                    <input type="password" id="password" name="password" class="form-input" 
                           placeholder=" " required>
                    <label for="password" class="form-label">Password</label>
                    <button type="button" class="password-toggle" id="togglePassword">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                
                <button type="submit" class="btn-submit">Sign In</button>
                
                <div class="action-links">
                    <a href="{{ route('password.request') }}">Forgot your password?</a>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('username').focus();
        });
        
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordField = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordField.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    </script>
</body>
</html>