<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kost Ditolak</title>
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
            background-color: #ef4444;
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
        .error-box {
            background-color: #fee2e2;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #ef4444;
            border-radius: 4px;
        }
        .error-box p {
            margin: 0 0 12px 0;
            color: #7f1d1d;
        }
        .error-box p:last-child {
            margin-bottom: 0;
        }
        .error-box strong {
            color: #991b1b;
        }
        ul {
            margin: 15px 0;
            padding-left: 20px;
        }
        ul li {
            margin: 8px 0;
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
            <h1>Kost Ditolak</h1>
        </div>
        
        <div class="content">
            <p>Halo {{ $kost->owner->name }},</p>
            
            <p>Submission kost Anda "<strong>{{ $kost->name }}</strong>" telah ditolak oleh Super Admin.</p>
            
            <div class="error-box">
                <p><strong>Alasan Penolakan:</strong></p>
                <p>{{ $kost->rejected_reason }}</p>
            </div>
            
            <p><strong>Yang Perlu Dilakukan:</strong></p>
            <ul>
                <li>Tinjau alasan penolakan dengan seksama</li>
                <li>Edit kost Anda untuk memperbaiki masalah yang disebutkan</li>
                <li>Submit ulang kost setelah perbaikan selesai</li>
            </ul>
            
            <div class="button-container">
                <a href="{{ url('/admin/kosts/' . $kost->id . '/edit') }}" class="button">
                    Perbaiki Kost
                </a>
            </div>
        </div>
        
        <div class="footer">
            <p>Ini adalah notifikasi otomatis dari SewaKost.</p>
        </div>
    </div>
</body>
</html>
