<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isPasswordReset ? 'Password Reset' : 'Welcome to Admin Dashboard' }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: #f7f7f7;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 6px;
        }
        .header {
            background: #7f56d9;
            color: #fff;
            padding: 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 22px;
        }
        .content {
            padding: 20px;
            font-size: 15px;
        }
        .greeting {
            font-weight: bold;
            margin-bottom: 10px;
        }
        .credentials-box {
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 15px;
            margin: 20px 0;
            background: #fafafa;
        }
        .credentials-box h3 {
            margin: 0 0 10px;
            font-size: 14px;
            color: #7f56d9;
        }
        .credential-item {
            margin: 8px 0;
            font-size: 14px;
        }
        .credential-label {
            font-weight: bold;
            display: inline-block;
            width: 80px;
        }
        .credential-value {
            font-family: monospace;
            background: #eee;
            padding: 3px 6px;
            border-radius: 3px;
        }
        .login-button {
            display: inline-block;
            background: #7f56d9;
            color: #fff !important;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: bold;
            margin: 20px 0;
        }
        .warning-box {
            background: #fff3cd;
            border: 1px solid #ffe08a;
            padding: 10px;
            border-radius: 4px;
            font-size: 14px;
            margin: 20px 0;
        }
        .footer {
            background: #f1f1f1;
            padding: 15px;
            text-align: center;
            font-size: 13px;
            color: #666;
            border-top: 1px solid #ddd;
        }
        .footer a {
            color: #7f56d9;
            text-decoration: none;
        }
        @media (max-width: 600px) {
            .container {
                margin: 10px;
            }
            .credential-label {
                display: block;
                margin-bottom: 3px;
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
                <p>Welcome to {{ $appName }}! An admin account has been created for you with the following credentials:</p>
            @endif

            <div class="credentials-box">
                <h3>{{ $isPasswordReset ? 'New Login Credentials' : 'Your Login Credentials' }}</h3>
                <div class="credential-item">
                    <span class="credential-label">Email:</span>
                    <span class="credential-value">{{ $admin->email }}</span>
                </div>
                <div class="credential-item">
                    <span class="credential-label">Password:</span>
                    <span class="credential-value">{{ $password }}</span>
                </div>
            </div>

            <div class="warning-box">
                <strong>⚠️ Security Notice:</strong>
                Please log in and change your password immediately after your first login.
            </div>

            <div style="text-align: center;">
                <a href="{{ $loginUrl }}" class="login-button">🚀 Access Admin Dashboard</a>
            </div>

            @if(!$isPasswordReset)
                <p>As an admin, you'll have access to:</p>
                <ul>
                    <li>User management</li>
                    <li>Content moderation</li>
                    <li>Analytics and reports</li>
                    <li>Admin settings</li>
                </ul>
            @endif

            <p style="font-size: 13px; color: #555;">
                If you have any questions, please contact our support team.
            </p>
        </div>

        <div class="footer">
            <p>
                This email was sent from {{ $appName }}<br>
                <a href="{{ config('app.url') }}">{{ config('app.url') }}</a>
            </p>
            <p style="font-size: 12px; color: #999;">
                This is an automated message, please do not reply.
            </p>
        </div>
    </div>
</body>
</html>
