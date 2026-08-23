<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Kost Submission</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f3f4f6;
        }
        .email-container {
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .header {
            background-color: #3b82f6;
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .content {
            padding: 30px;
        }
        .info-box {
            background-color: #f9fafb;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #3b82f6;
            border-radius: 4px;
        }
        .info-box p {
            margin: 0 0 12px 0;
        }
        .info-box p:last-child {
            margin-bottom: 0;
        }
        .info-box strong {
            color: #1f2937;
        }
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        .button {
            display: inline-block;
            background-color: #3b82f6;
            color: white;
            padding: 14px 28px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 16px;
        }
        .button:hover {
            background-color: #2563eb;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #6b7280;
            font-size: 14px;
            border-top: 1px solid #e5e7eb;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>New Kost Submission</h1>
        </div>
        
        <div class="content">
            <p>Hello Super Admin,</p>
            
            <p>A new kost has been submitted for review:</p>
            
            <div class="info-box">
                <p><strong>Kost Name:</strong> {{ $kost->name }}</p>
                <p><strong>Owner:</strong> {{ $kost->owner->name }}</p>
                <p><strong>Category:</strong> {{ $kost->categories->pluck('nama')->join(', ') }}</p>
            </div>
            
            <p>Please review the submission details and either approve or reject it.</p>
            
            <div class="button-container">
                <a href="{{ url('/super-admin/kost-submissions/' . $kost->id) }}" class="button">
                    Review Submission
                </a>
            </div>
        </div>
        
        <div class="footer">
            <p>This is an automated notification from SewaKost.</p>
        </div>
    </div>
</body>
</html>
