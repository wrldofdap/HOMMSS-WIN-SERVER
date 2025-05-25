<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Contact Form Submission</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .content {
            background-color: #ffffff;
            padding: 20px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
        }
        .field {
            margin-bottom: 15px;
        }
        .field-label {
            font-weight: bold;
            color: #495057;
            margin-bottom: 5px;
        }
        .field-value {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 3px;
            border-left: 3px solid #007bff;
        }
        .footer {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            font-size: 12px;
            color: #6c757d;
        }
        .logo {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo h1 {
            color: #007bff;
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">
            <h1>HOMMSS Corporation</h1>
        </div>
        <h2 style="margin: 0; color: #495057;">New Contact Form Submission</h2>
    </div>

    <div class="content">
        <p>You have received a new message through the HOMMSS website contact form:</p>

        <div class="field">
            <div class="field-label">Name:</div>
            <div class="field-value">{{ $name }}</div>
        </div>

        <div class="field">
            <div class="field-label">Email:</div>
            <div class="field-value">{{ $email }}</div>
        </div>

        @if(!empty($phone))
        <div class="field">
            <div class="field-label">Phone:</div>
            <div class="field-value">{{ $phone }}</div>
        </div>
        @endif

        <div class="field">
            <div class="field-label">Subject:</div>
            <div class="field-value">{{ $subject }}</div>
        </div>

        <div class="field">
            <div class="field-label">Message:</div>
            <div class="field-value">{{ nl2br(e($message)) }}</div>
        </div>
    </div>

    <div class="footer">
        <p><strong>Contact Information:</strong></p>
        <p>
            HOMMSS Corporation<br>
            Blk1 Lot 1 Ph6 Glocal St., Sterling Industrial Park<br>
            Libtong, Meycauayan Bulacan, Philippines 3020<br>
            Phone: (044) 816 7442<br>
            Email: hommss@gmail.com
        </p>
        <p><em>This email was sent automatically from the HOMMSS website contact form.</em></p>
    </div>
</body>
</html>
