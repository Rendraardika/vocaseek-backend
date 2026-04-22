<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Undangan Aktivasi Akun Admin</title>
</head>
<body style="margin:0;background:#f4f7fb;font-family:Arial,sans-serif;color:#1f2937;">
    <div style="max-width:640px;margin:0 auto;padding:32px 20px;">
        <div style="background:#ffffff;border:1px solid #e5e7eb;border-radius:20px;overflow:hidden;box-shadow:0 10px 30px rgba(15,23,42,0.06);">
            <div style="padding:28px 32px;background:linear-gradient(135deg,#1e3a8a,#334155);color:#ffffff;">
                <div style="font-size:12px;letter-spacing:0.16em;text-transform:uppercase;opacity:0.78;">Vocaseek Internal Dashboard</div>
                <h1 style="margin:12px 0 0;font-size:28px;line-height:1.2;">Undangan Aktivasi Akun Admin</h1>
            </div>

            <div style="padding:32px;">
                <p style="margin:0 0 16px;">Halo <strong>{{ $invitation->name }}</strong>,</p>
                <p style="margin:0 0 16px;line-height:1.7;">
                    Anda diundang untuk mengaktifkan akun <strong>{{ strtoupper(str_replace('_', ' ', $invitation->role)) }}</strong>
                    pada sistem internal Vocaseek.
                </p>
                <p style="margin:0 0 16px;line-height:1.7;">
                    Untuk melanjutkan, silakan klik tombol di bawah ini dan buat password Anda sendiri.
                </p>

                <div style="margin:28px 0;text-align:center;">
                    <a href="{{ $activationUrl }}"
                       style="display:inline-block;padding:14px 26px;border-radius:12px;background:#f6c400;color:#183153;text-decoration:none;font-weight:700;">
                        Aktifkan Akun
                    </a>
                </div>

                <div style="padding:16px 18px;border-radius:14px;background:#f8fafc;border:1px solid #e5e7eb;">
                    <p style="margin:0 0 8px;font-size:14px;"><strong>Email akun</strong>: {{ $invitation->email }}</p>
                    <p style="margin:0 0 8px;font-size:14px;"><strong>Masa berlaku</strong>: {{ $invitation->expires_at?->timezone(config('app.timezone'))->format('d M Y H:i') }} WIB</p>
                    <p style="margin:0;font-size:14px;color:#64748b;">Tautan ini hanya dapat digunakan satu kali dan akan kedaluwarsa secara otomatis.</p>
                </div>

                <p style="margin:24px 0 0;line-height:1.7;">
                    Jika tombol tidak dapat dibuka, gunakan tautan berikut:
                    <br>
                    <a href="{{ $activationUrl }}" style="color:#2563eb;word-break:break-all;">{{ $activationUrl }}</a>
                </p>

                <p style="margin:24px 0 0;line-height:1.7;">
                    Jika Anda merasa tidak pernah menerima undangan ini, abaikan email ini.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
