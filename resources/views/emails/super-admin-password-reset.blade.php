<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Super Admin Password Reset</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header .crown-icon {
            font-size: 3rem;
            color: #f59e0b;
            margin-bottom: 1rem;
            display: block;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
        }
        .content {
            padding: 40px 30px;
        }
        .content h2 {
            color: #333;
            margin-bottom: 20px;
        }
        .content p {
            margin-bottom: 20px;
            color: #666;
        }
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin: 20px 0;
            transition: all 0.3s ease;
        }
        .button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        .footer {
            background: #f8f9fa;
            padding: 20px 30px;
            text-align: center;
            color: #666;
            font-size: 14px;
        }
        .warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .admin-badge {
            background: #f59e0b;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 10px;
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <i class="fas fa-crown crown-icon">👑</i>
            <h1>ST ZK Digital Media</h1>
            <p style="margin: 10px 0 0 0; opacity: 0.9;">Super Admin Panel</p>
            <div class="admin-badge">Super Admin Access</div>
        </div>
        
        <div class="content">
            <h2>Password Reset Request</h2>
            
            <p>Hello {{ $superAdmin->name }},</p>
            
            <p>You are receiving this email because we received a password reset request for your <strong>Super Admin</strong> account.</p>
            
            <div style="text-align: center;">
                <a href="{{ $resetUrl }}" class="button">Reset Super Admin Password</a>
            </div>
            
            <div class="warning">
                <strong>Security Notice:</strong> This is a super admin password reset link with elevated privileges. This link will expire in 60 minutes for security reasons.
            </div>
            
            <p>If you did not request a password reset, no further action is required. Your super admin account remains secure.</p>
            
            <p>If you're having trouble clicking the "Reset Super Admin Password" button, copy and paste the URL below into your web browser:</p>
            <p style="word-break: break-all; color: #667eea;">{{ $resetUrl }}</p>
        </div>
        
        <div class="footer">
            <p>This is an automated message from ST ZK Digital Media Super Admin System.</p>
            <p>Please do not reply to this email.</p>
            <p><strong>Super Admin Access Only</strong> - This email contains privileged information.</p>
        </div>
    </div>
</body>
</html>