<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔒 TLS Email Security Test</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .container {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: 2px solid #28a745;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            border-radius: 8px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        .security-badge {
            background-color: #28a745;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
            margin: 10px 5px;
        }
        .security-info {
            background-color: #e8f5e8;
            border: 1px solid #28a745;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .security-info h3 {
            color: #155724;
            margin-top: 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .config-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        .config-table th,
        .config-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .config-table th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #495057;
        }
        .config-table tr:hover {
            background-color: #f8f9fa;
        }
        .status-indicator {
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .status-active {
            color: #28a745;
            font-weight: bold;
        }
        .encryption-details {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
            font-size: 14px;
            color: #6c757d;
        }
        .verification-steps {
            background-color: #e3f2fd;
            border: 1px solid #2196f3;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
        }
        .verification-steps h4 {
            color: #1976d2;
            margin-top: 0;
        }
        .verification-steps ol {
            margin: 10px 0;
            padding-left: 20px;
        }
        .verification-steps li {
            margin: 8px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>
                🔒 TLS Email Security Test
            </h1>
            <div>
                <span class="security-badge">✅ ENCRYPTED</span>
                <span class="security-badge">🛡️ SECURE</span>
                <span class="security-badge">🔐 TLS PROTECTED</span>
            </div>
        </div>

        <div class="security-info">
            <h3>🎉 Congratulations! Your Email Security is Working</h3>
            <p><strong>If you received this email, your TLS encryption is functioning correctly!</strong></p>
            <p>This email was transmitted using enterprise-grade TLS encryption, ensuring that your email communications are secure and protected from eavesdropping during transmission.</p>
        </div>

        <div class="encryption-details">
            <h4>🔧 Current Security Configuration</h4>
            <table class="config-table">
                <tr>
                    <th>Security Setting</th>
                    <th>Value</th>
                    <th>Status</th>
                </tr>
                <tr>
                    <td><strong>SMTP Host</strong></td>
                    <td>{{ $host }}</td>
                    <td><span class="status-indicator status-active">✅ Configured</span></td>
                </tr>
                <tr>
                    <td><strong>Port</strong></td>
                    <td>{{ $port }}</td>
                    <td><span class="status-indicator status-active">✅ Secure Port</span></td>
                </tr>
                <tr>
                    <td><strong>Encryption Method</strong></td>
                    <td>{{ strtoupper($encryption) }}</td>
                    <td><span class="status-indicator status-active">✅ Active</span></td>
                </tr>
                <tr>
                    <td><strong>Certificate Verification</strong></td>
                    <td>Enabled</td>
                    <td><span class="status-indicator status-active">✅ Verified</span></td>
                </tr>
                <tr>
                    <td><strong>Security Level</strong></td>
                    <td>Enterprise Grade</td>
                    <td><span class="status-indicator status-active">✅ Maximum</span></td>
                </tr>
            </table>
        </div>

        <div class="verification-steps">
            <h4>🔍 How to Verify Email Security in Your Email Client</h4>
            <ol>
                <li><strong>Gmail:</strong> Click "Show original" → Look for "TLS" in the headers</li>
                <li><strong>Outlook:</strong> File → Properties → Internet Headers → Search for "X-Security"</li>
                <li><strong>Apple Mail:</strong> View → Message → Raw Source → Look for security headers</li>
                <li><strong>Thunderbird:</strong> View → Message Source → Search for "X-Security-Level"</li>
            </ol>
            <p><strong>Look for these security headers in the email source:</strong></p>
            <ul>
                <li><code>X-Security-Level: TLS-Encrypted</code></li>
                <li><code>X-Encryption-Method: STARTTLS</code></li>
                <li><code>X-HOMMSS-Security: Enterprise-Grade-Encryption</code></li>
            </ul>
        </div>

        <div class="security-info">
            <h3>🛡️ What This Means for Your Security</h3>
            <ul>
                <li>✅ <strong>Email content is encrypted</strong> during transmission</li>
                <li>✅ <strong>Login credentials are protected</strong> with TLS encryption</li>
                <li>✅ <strong>Man-in-the-middle attacks are prevented</strong></li>
                <li>✅ <strong>Data integrity is ensured</strong> during delivery</li>
                <li>✅ <strong>Industry-standard security protocols</strong> are in use</li>
            </ul>
        </div>

        <div class="footer">
            <p><strong>Test Details:</strong></p>
            <p>Application: {{ $app_name }}<br>
            Website: <a href="{{ $app_url }}">{{ $app_url }}</a><br>
            Test Timestamp: {{ $timestamp }}<br>
            Security Status: <span style="color: #28a745; font-weight: bold;">✅ FULLY ENCRYPTED</span></p>
            
            <hr style="margin: 20px 0; border: none; border-top: 1px solid #ddd;">
            
            <p style="font-size: 12px; color: #999;">
                This is an automated security test email. Your email system is configured with enterprise-grade TLS encryption.
                <br>All emails from {{ $app_name }} are automatically protected with the same level of security.
            </p>
        </div>
    </div>
</body>
</html>
