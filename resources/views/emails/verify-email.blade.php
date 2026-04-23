<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Email Vocaseek</title>
</head>
<body style="margin:0;background:#f4f6fb;font-family:Arial,sans-serif;color:#1f2937;">
    <div style="max-width:420px;margin:0 auto;padding:36px 16px;">
        <div style="text-align:center;margin-bottom:14px;">
            <img src="{{ url('/vocaseeklogo.png') }}" alt="Vocaseek" style="width:128px;max-width:100%;height:auto;">
        </div>

        <div style="background:#ffffff;border:1px solid #e7ebf3;border-radius:22px;padding:28px 24px;box-shadow:0 12px 30px rgba(31,41,55,0.08);">
            <h1 style="margin:0 0 10px;font-size:22px;line-height:1.25;color:#12233d;text-align:center;">Verifikasi Email Anda</h1>
            <p style="margin:0 0 18px;font-size:14px;line-height:1.7;color:#6b7280;text-align:center;">
                {{ $introText }}
            </p>

            <p style="margin:0 0 14px;font-size:14px;line-height:1.7;">Halo <strong>{{ $name }}</strong>,</p>
            <p style="margin:0 0 18px;font-size:14px;line-height:1.7;">
                Klik tombol di bawah ini untuk melanjutkan verifikasi email akun Anda.
            </p>

            <div style="margin:0 0 18px;">
                <a href="{{ $verificationUrl }}"
                   style="display:inline-block;width:100%;box-sizing:border-box;padding:14px 18px;border-radius:12px;background:#f2b90d;color:#12233d;text-decoration:none;font-weight:700;text-align:center;">
                    Verifikasi Email
                </a>
            </div>

            <p style="margin:0 0 10px;font-size:14px;line-height:1.7;color:#37957f;text-align:center;">
                Jika email terdaftar, tautan verifikasi akan tetap berlaku selama {{ $expiresInMinutes }} menit.
            </p>

            <p style="margin:0 0 16px;font-size:13px;line-height:1.7;color:#6b7280;text-align:center;">
                Email tujuan: <strong style="color:#1f2937;">{{ $email }}</strong>
            </p>

            <p style="margin:0 0 12px;font-size:13px;line-height:1.7;color:#6b7280;">
                Jika tombol tidak dapat dibuka, gunakan tautan berikut:
            </p>
            <p style="margin:0 0 16px;font-size:13px;line-height:1.7;">
                <a href="{{ $verificationUrl }}" style="color:#1d4ed8;word-break:break-all;">{{ $verificationUrl }}</a>
            </p>

            <p style="margin:0;font-size:13px;line-height:1.7;color:#6b7280;">
                Jika Anda tidak merasa membuat akun ini, abaikan email ini.
            </p>
        </div>

        <div style="margin-top:16px;font-size:11px;color:#9ca3af;text-align:center;">
            (c) 2026 VOCASEEK INC. Semua hak dilindungi.
        </div>
    </div>
</body>
</html>
