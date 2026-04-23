<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Email Vocaseek</title>
</head>
<body style="margin:0;background:#edf3fb;font-family:Arial,sans-serif;color:#1f2937;">
    <div style="max-width:640px;margin:0 auto;padding:32px 20px;">
        <div style="background:#ffffff;border:1px solid #dbe5f0;border-radius:24px;overflow:hidden;box-shadow:0 18px 40px rgba(15,23,42,0.08);">
            <div style="padding:34px 32px;background:linear-gradient(135deg,#1d4ed8 0%,#0f172a 58%,#f59e0b 100%);color:#ffffff;">
                <div style="font-size:12px;letter-spacing:0.18em;text-transform:uppercase;opacity:0.8;">Vocaseek Account Security</div>
                <h1 style="margin:12px 0 10px;font-size:30px;line-height:1.2;">Verifikasi Email Anda</h1>
                <p style="margin:0;max-width:460px;font-size:15px;line-height:1.7;color:rgba(255,255,255,0.9);">
                    Satu langkah lagi untuk mengaktifkan akses Anda ke ekosistem Vocaseek.
                </p>
            </div>

            <div style="padding:32px;">
                <p style="margin:0 0 16px;line-height:1.8;">Halo <strong>{{ $name }}</strong>,</p>
                <p style="margin:0 0 16px;line-height:1.8;">{{ $introText }}</p>
                <p style="margin:0 0 20px;line-height:1.8;">Klik tombol di bawah ini untuk melanjutkan proses verifikasi email Anda.</p>

                <div style="margin:30px 0;text-align:center;">
                    <a href="{{ $verificationUrl }}"
                       style="display:inline-block;padding:15px 28px;border-radius:14px;background:#f59e0b;color:#12233d;text-decoration:none;font-weight:700;box-shadow:0 12px 24px rgba(245,158,11,0.28);">
                        Verifikasi Email
                    </a>
                </div>

                <div style="padding:18px;border-radius:18px;background:linear-gradient(180deg,#f8fbff 0%,#fff8ef 100%);border:1px solid #e5e7eb;">
                    <p style="margin:0 0 8px;font-size:14px;"><strong>Email tujuan</strong>: {{ $email }}</p>
                    <p style="margin:0 0 8px;font-size:14px;"><strong>Masa berlaku link</strong>: {{ $expiresInMinutes }} menit</p>
                    <p style="margin:0;font-size:14px;color:#64748b;">Jika link sudah kedaluwarsa, Anda bisa minta kirim ulang verifikasi dari aplikasi.</p>
                </div>

                <p style="margin:24px 0 0;line-height:1.8;">
                    Jika tombol tidak dapat dibuka, gunakan tautan berikut:
                    <br>
                    <a href="{{ $verificationUrl }}" style="color:#1d4ed8;word-break:break-all;">{{ $verificationUrl }}</a>
                </p>

                <p style="margin:24px 0 0;line-height:1.8;">Jika Anda tidak merasa membuat akun ini, abaikan email ini.</p>

                <div style="margin-top:28px;padding-top:20px;border-top:1px solid #e5e7eb;color:#64748b;font-size:14px;line-height:1.8;">
                    Salam hangat,<br>
                    <strong style="color:#1f2937;">Tim Vocaseek</strong>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
