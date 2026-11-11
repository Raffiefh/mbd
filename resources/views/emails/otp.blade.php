<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Kode Verifikasi OTP</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333;">
    <h2>Halo User NongkrongID</h2>
    <p>Terima kasih sudah bergabung pada platform kami <strong>NongkrongID</strong>.</p>
    <p>Berikut adalah kode verifikasi OTP Anda (Kode ini akan berlaku selama 5 menit):</p>
    
    <div style="background: #f4f4f4; padding: 15px; font-size: 24px; font-weight: bold; letter-spacing: 5px; text-align: center; width: 200px;">
        {{ $otp }}
    </div>

    <p style="margin-top: 20px;"> Mohon jangan berikan kode ini kepada siapa pun.</p>
    <p style="margin-top: 20px;"> Terima kasih </p>
</body>
</html>