<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kost Disetujui</title>
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
            background-color: #10b981;
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
        .success-box {
            background-color: #d1fae5;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #10b981;
            border-radius: 4px;
        }
        .success-box p {
            margin: 0;
            color: #065f46;
        }
        .success-box strong {
            color: #047857;
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
            background-color: #10b981;
            color: white;
            padding: 14px 28px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 16px;
        }
        .button:hover {
            background-color: #059669;
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
            <h1>Kost Disetujui ✓</h1>
        </div>
        
        <div class="content">
            <p>Halo {{ $kost->owner->name }},</p>
            
            <div class="success-box">
                <p><strong>Kabar baik!</strong> Kost Anda "<strong>{{ $kost->name }}</strong>" telah disetujui oleh Super Admin.</p>
            </div>
            
            <p><strong>Langkah Selanjutnya:</strong></p>
            <ul>
                <li>Anda sekarang dapat mempublikasikan kost agar terlihat oleh penyewa</li>
                <li>Tinjau detail kost untuk memastikan semuanya akurat</li>
                <li>Setelah dipublikasikan, penyewa akan dapat melihat dan memesan kost Anda</li>
            </ul>
            
            <div class="button-container">
                <a href="{{ url('/admin/kosts/' . $kost->id) }}" class="button">
                    Lihat Kost
                </a>
            </div>
        </div>
        
        <div class="footer">
            <p>Ini adalah notifikasi otomatis dari SewaKost.</p>
        </div>
    </div>
</body>
</html>
