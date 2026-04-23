<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password Vocaseek</title>
</head>
<body style="margin:0;background:#edf3fb;font-family:Arial,sans-serif;color:#1f2937;">
    <div style="max-width:640px;margin:0 auto;padding:32px 20px;">
        <div style="background:#ffffff;border:1px solid #dbe5f0;border-radius:24px;overflow:hidden;box-shadow:0 18px 40px rgba(15,23,42,0.08);">
            <div style="padding:28px 32px 0;">
                <div style="display:inline-block;padding:8px 14px;border-radius:999px;background:#fff6db;color:#b7791f;font-size:12px;font-weight:700;letter-spacing:0.14em;text-transform:uppercase;">
                    Vocaseek Account Recovery
                </div>
                <h1 style="margin:18px 0 10px;font-size:30px;line-height:1.2;color:#12233d;">Buat Password Baru</h1>
                <p style="margin:0;max-width:480px;font-size:15px;line-height:1.7;color:#52627a;">
                    Kami menerima permintaan untuk mengatur ulang kata sandi akun Vocaseek Anda.
                </p>
            </div>

            <div style="padding:28px 32px 32px;">
                <p style="margin:0 0 16px;line-height:1.8;">Halo <strong>{{ $name ?? 'Pengguna Vocaseek' }}</strong>,</p>
                <p style="margin:0 0 20px;line-height:1.8;">Klik tombol di bawah ini untuk membuat kata sandi baru dan melanjutkan akses ke akun Anda.</p>

                <div style="margin:30px 0;text-align:center;">
                    <a href="{{ $resetLink }}"
                       style="display:inline-block;padding:15px 28px;border-radius:14px;background:#f59e0b;color:#12233d;text-decoration:none;font-weight:700;box-shadow:0 12px 24px rgba(245,158,11,0.28);">
                        Reset Password
                    </a>
                </div>

                <div style="padding:18px;border-radius:18px;background:linear-gradient(180deg,#f8fbff 0%,#fff8ef 100%);border:1px solid #e5e7eb;">
                    <p style="margin:0 0 8px;font-size:14px;"><strong>Masa berlaku link</strong>: {{ $expiresInMinutes }} menit</p>
                    <p style="margin:0;font-size:14px;color:#64748b;">Jika Anda tidak meminta reset password, abaikan email ini dan akun Anda akan tetap aman.</p>
                </div>

                <p style="margin:24px 0 0;line-height:1.8;">
                    Jika tombol tidak dapat dibuka, gunakan tautan berikut:
                    <br>
                    <a href="{{ $resetLink }}" style="color:#1d4ed8;word-break:break-all;">{{ $resetLink }}</a>
                </p>

                <div style="margin-top:28px;padding-top:20px;border-top:1px solid #e5e7eb;color:#64748b;font-size:14px;line-height:1.8;">
                    Salam hangat,<br>
                    <strong style="color:#1f2937;">Tim Vocaseek</strong>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
