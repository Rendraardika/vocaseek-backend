<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Reset Password Vocaseek</title>
</head>
<body style="font-family: Arial, sans-serif; color: #1f2937; line-height: 1.6;">
    <p>Halo {{ $name ?? 'Pengguna Vocaseek' }},</p>

    <p>Kami menerima permintaan untuk mengatur ulang kata sandi akun Anda.</p>

    <p>
        Klik tautan berikut untuk membuat kata sandi baru:
        <br>
        <a href="{{ $resetLink }}">{{ $resetLink }}</a>
    </p>

    <p>Tautan ini berlaku selama {{ $expiresInMinutes }} menit.</p>

    <p>Jika Anda tidak merasa meminta reset password, abaikan email ini.</p>

    <p>Terima kasih,<br>Vocaseek</p>
</body>
</html>
