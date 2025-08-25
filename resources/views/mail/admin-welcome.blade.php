<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isPasswordReset ? 'Password Reset' : 'Welcome to Admin Dashboard' }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 300;
        }
        .content {
            padding: 40px 30px;
        }
        .greeting {
            font-size: 18px;
            margin-bottom: 20px;
            color: #333;
        }
        .credentials-box {
            background: #f8f9ff;
            border: 2px solid #e1e5fe;
            border-radius: 8px;
            padding: 25px;
            margin: 25px 0;
            text-align: center;
        }
        .credentials-box h3 {
            margin-top: 0;
            color: #5c6bc0;
            font-size: 16px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .credential-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 15px 0;
            padding: 10px 15px;
            background: white;
            border-radius: 5px;
            border-left: 4px solid #5c6bc0;
        }
        .credential-label {
            font-weight: 600;
            color: #666;
        }
        .credential-value {
            font-family: 'Courier New', monospace;
            background: #e8eaf6;
            padding: 5px 10px;
            border-radius: 4px;
            color: #333;
            font-weight: bold;
        }
        .login-button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            margin: 20px 0;
            transition: transform 0.2s;
        }
        .login-button:hover {
            transform: translateY(-2px);
        }
        .warning-box {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .warning-box strong {
            display: block;
            margin-bottom: 5px;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            color: #666;
            font-size: 14px;
            border-top: 1px solid #e9ecef;
        }
        .footer a {
            color: #667eea;
            text-decoration: none;
        }
        @media (max-width: 600px) {
            .container {
                margin: 10px;
                border-radius: 5px;
            }
            .content {
                padding: 20px 15px;
            }
            .credential-item {
                flex-direction: column;
                text-align: left;
            }
            .credential-value {
                margin-top: 5px;
                align-self: stretch;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $isPasswordReset ? '🔐 Password Reset' : '🎉 Welcome to ' . $appName }}</h1>
        </div>
        
        <div class="content">
            <div class="greeting">
                @if($isPasswordReset)
                    Hello {{ $admin->name ?? 'Admin' }},
                @else
                    Hello {{ $admin->name ?? 'there' }},
                @endif
            </div>

            @if($isPasswordReset)
                <p>Your admin account password has been reset. Here are your new login credentials:</p>
            @else
                <p>Welcome to the {{ $appName }}! An admin account has been created for you with the following credentials:</p>
            @endif

            <div class="credentials-box">
                <h3>{{ $isPasswordReset ? 'New Login Credentials' : 'Your Login Credentials' }}</h3>
                
                <div class="credential-item">
                    <span class="credential-label">📧 Email:</span>
                    <span class="credential-value">{{ $admin->email }}</span>
                </div>
                
                <div class="credential-item">
                    <span class="credential-label">🔑 Password:</span>
                    <span class="credential-value">{{ $password }}</span>
                </div>
            </div>

            <div class="warning-box">
                <strong>⚠️ Important Security Notice:</strong>
                Please log in and change your password immediately after your first login. This temporary password should not be shared with anyone.
            </div>

            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ $loginUrl }}" class="login-button">
                    🚀 Access Admin Dashboard
                </a>
            </div>

            @if(!$isPasswordReset)
                <p>As an admin, you'll have access to:</p>
                <ul style="color: #666; padding-left: 20px;">
                    <li>User management and oversight</li>
                    <li>Content moderation tools</li>
                    <li>System analytics and reports</li>
                    <li>Administrative settings</li>
                </ul>
            @endif

            <p style="color: #666; font-size: 14px; margin-top: 30px;">
                If you have any questions or need assistance, please don't hesitate to contact our support team.
            </p>
        </div>

        <div class="footer">
            <p>
                This email was sent from {{ $appName }}<br>
                <a href="{{ config('app.url') }}">{{ config('app.url') }}</a>
            </p>
            <p style="margin-top: 10px; font-size: 12px; color: #999;">
                This is an automated message, please do not reply to this email.
            </p>
        </div>
    </div>
</body>
</html>