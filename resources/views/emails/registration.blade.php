<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Registration Successful</title>
    <style>
        body {
            font-family: 'Inter', Helvetica, Arial, sans-serif;
            background-color: #f8fafc;
            color: #0f172a;
            margin: 0;
            padding: 40px 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
        }
        .logo {
            font-size: 24px;
            font-weight: 700;
            color: #4f46e5;
            margin-bottom: 24px;
        }
        h1 {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 16px;
        }
        p {
            font-size: 15px;
            line-height: 1.6;
            color: #475569;
            margin-bottom: 16px;
        }
        .badge {
            display: inline-block;
            background-color: #fef3c7;
            color: #d97706;
            padding: 6px 12px;
            font-size: 13px;
            font-weight: 500;
            border-radius: 9999px;
            margin-bottom: 24px;
        }
        .footer {
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid #e2e8f0;
            font-size: 13px;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">WhatsApp SaaS</div>
        <h1>Hello {{ $user->name }},</h1>
        <p>Thank you for registering on our WhatsApp SaaS platform.</p>
        <div class="badge">Status: Pending Administrator Approval</div>
        <p>Your registration details are currently being reviewed by our administrative team. We will notify you once your account has been activated.</p>
        <p>If you have any questions, feel free to reply directly to this email or contact support.</p>
        
        <div class="footer">
            Regards,<br>
            The WhatsApp SaaS Team
        </div>
    </div>
</body>
</html>
