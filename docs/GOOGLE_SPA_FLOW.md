# Google Login Flow for Frontend SPA

Dokumen ini menjelaskan flow Google login untuk frontend terpisah yang memakai networking call ke backend.

## Kenapa Butuh Flow Ini

Flow lama `GET /api/auth/google` cocok untuk redirect browser penuh.

Kalau frontend kamu adalah SPA React, Next.js, mobile app, atau domain terpisah, biasanya lebih nyaman:
- frontend login ke Google lebih dulu
- frontend dapat `access_token` Google
- frontend kirim token itu ke backend
- backend balas token API Vocaseek

## Endpoint Backend

- `POST /api/auth/google/token`

Body:

```json
{
  "access_token": "google-access-token"
}
```

Response sukses:

```json
{
  "status": "success",
  "message": "Login Google berhasil.",
  "token": "sanctum-token",
  "role": "intern",
  "user": {
    "user_id": 14,
    "nama": "Rendra Ardika",
    "email": "rendra@example.com",
    "google_id": "1234567890"
  }
}
```

## Flow Frontend

1. Frontend meminta login ke Google memakai Google Identity Services.
2. Frontend menerima `access_token` dari Google.
3. Frontend memanggil `POST /api/auth/google/token`.
4. Backend memverifikasi token ke Google lewat Socialite.
5. Backend mencari atau membuat user lokal.
6. Backend mengembalikan token API Vocaseek.
7. Frontend menyimpan token itu dan memakai header bearer untuk request berikutnya.

## Contoh Header Setelah Login

```http
Authorization: Bearer {sanctum_token}
Accept: application/json
```

## Peran CORS

Kalau frontend berjalan di origin berbeda, misalnya:
- `http://localhost:3000`
- `http://localhost:5173`

maka browser akan memblokir request ke backend kalau backend tidak mengizinkan origin tersebut.

Itulah fungsi `config/cors.php`:
- menentukan origin mana yang boleh akses API
- menentukan method dan header yang diizinkan
- memungkinkan cookie/credential bila dibutuhkan

## File CORS yang Ditambahkan

- [cors.php](/c:/laragon/www/VOCASEEK/vocaseek/config/cors.php)

Konfigurasi env yang dipakai:
- `CORS_ALLOWED_ORIGINS`
- `SANCTUM_STATEFUL_DOMAINS`

## Kapan Pakai Redirect Flow dan Kapan Pakai Token Flow

Pakai `GET /api/auth/google` bila:
- frontend dan backend menyatu di web Laravel
- login dilakukan dengan redirect halaman penuh

Pakai `POST /api/auth/google/token` bila:
- frontend adalah SPA terpisah
- frontend ingin mengontrol login state sendiri
- integrasi dilakukan lewat networking call/API
