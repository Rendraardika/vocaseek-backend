# Vocaseek Frontend API Handoff

Dokumen ini dipakai sebagai pegangan tim frontend agar bisa langsung integrasi ke backend melalui HTTP request.

## Base URL

- Local API: `http://localhost:8000/api`
- Local Web OAuth redirect: `http://localhost:8000`

## Auth Model

- API login biasa memakai bearer token Sanctum.
- Header untuk endpoint yang butuh login:

```http
Authorization: Bearer {token}
Accept: application/json
```

- Endpoint untuk ambil user login:
  - `GET /api/me`

## Catatan Penting Google Login

- Google login saat ini memakai browser redirect OAuth, bukan pure JSON `fetch`.
- Flow yang dipakai frontend:
  1. Redirect browser ke `GET /api/auth/google`
  2. User pilih akun Google
  3. Backend callback ke Google, login/create user
  4. Untuk app Laravel web, user diarahkan ke dashboard

- Jika tim frontend adalah SPA/mobile terpisah dan ingin pure networking call, backend masih perlu flow khusus seperti:
  - login memakai Google ID token dari frontend, atau
  - callback yang me-redirect kembali ke frontend app dengan token

## Public Endpoints

### `GET /api/landing-stats`

Response:

```json
{
  "status": "success",
  "data": {
    "live_jobs": 10,
    "companies": 4,
    "candidates": 120
  }
}
```

### `GET /api/popular-vacancies`

Response:

```json
{
  "status": "success",
  "data": []
}
```

### `GET /api/partners`

Dipakai untuk daftar semua partner/perusahaan aktif yang bisa ditampilkan ke user intern atau halaman publik.

Query opsional:
- `search`
- `per_page`

Contoh:

```http
GET /api/partners?per_page=12
GET /api/partners?search=teknologi
```

Response:

```json
{
  "status": "success",
  "summary": {
    "total_partners": 12,
    "current_page": 1,
    "per_page": 12
  },
  "data": [
    {
      "id": 1,
      "company_id": 1,
      "nama_perusahaan": "PT Maju Teknologi",
      "company_name": "PT Maju Teknologi",
      "industri": "Teknologi",
      "industry": "Teknologi",
      "location": "Surabaya",
      "website_url": "https://contoh.com",
      "logo_url": "http://localhost:8000/storage/company/logos/logo.png",
      "banner_url": null,
      "active_jobs_count": 3,
      "display": {
        "title": "PT Maju Teknologi",
        "subtitle": "Teknologi",
        "image": "http://localhost:8000/storage/company/logos/logo.png",
        "meta": {
          "location": "Surabaya",
          "website": "https://contoh.com",
          "active_jobs_count": 3
        }
      }
    }
  ],
  "pagination": {
    "total": 12,
    "current_page": 1,
    "last_page": 1,
    "per_page": 12
  }
}
```

## Auth Endpoints

### `POST /api/register`

Content type:
- `multipart/form-data` untuk role `company`
- `application/json` untuk role `intern`
- Role `company` tidak langsung bisa login. Harus menunggu approval super admin.

Body minimal intern:

```json
{
  "nama": "Rendra",
  "email": "rendra@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "notelp": "08123456789",
  "role": "intern"
}
```

Body tambahan company:
- `nama_perusahaan`
- `nib`
- `loa_pdf`
- `akta_pdf`

Response sukses:

```json
{
  "status": "success",
  "message": "Registrasi Berhasil!",
  "token": "plain-text-token",
  "user": "Rendra",
  "role": "intern"
}
```

Response sukses untuk register company:

```json
{
  "status": "success",
  "message": "Registrasi berhasil! Menunggu verifikasi super admin sebelum bisa login.",
  "user": "PIC Company",
  "role": "company"
}
```

### `POST /api/login`

Body:

```json
{
  "email": "rendra@example.com",
  "password": "password123"
}
```

Response sukses:

```json
{
  "status": "success",
  "token": "plain-text-token",
  "role": "intern",
  "user": "Rendra"
}
```

Jika akun `company` belum diverifikasi super admin:

```json
{
  "status": "error",
  "message": "Akun company Anda belum disetujui super admin."
}
```

### `GET /api/me`

Header:
- `Authorization: Bearer {token}`

Response sukses:

```json
{
  "status": "success",
  "data": {
    "user_id": 1,
    "nama": "Rendra",
    "email": "rendra@example.com",
    "role": "intern",
    "notelp": "08123456789",
    "google_id": null
  }
}
```

### `POST /api/logout`

Header:
- `Authorization: Bearer {token}`

Response:

```json
{
  "status": "success",
  "message": "Berhasil Logout!"
}
```

### `POST /api/forgot-password`

Body:

```json
{
  "email": "rendra@example.com"
}
```

### `POST /api/reset-password`

Body:

```json
{
  "email": "rendra@example.com",
  "token": "reset-token",
  "password": "passwordBaru123",
  "password_confirmation": "passwordBaru123"
}
```

## Intern Endpoints

Semua endpoint intern membutuhkan:
- bearer token
- role user `intern`

### `GET /api/intern/profile`
### `PUT /api/intern/update-profile`

Untuk update profile, gunakan `multipart/form-data` jika upload file:
- `foto`
- `cv_pdf`
- `portofolio_pdf`

Field teks yang didukung:
- `tentang_saya`
- `tempat_lahir`
- `tanggal_lahir`
- `jenis_kelamin`
- `provinsi`
- `kabupaten`
- `detail_alamat`
- `universitas`
- `jurusan`
- `jenjang`
- `ipk`
- `tahun_masuk`
- `tahun_lulus`
- `linkedin`
- `instagram`
- `notelp`

### `POST /api/intern/start-test`

### `POST /api/intern/submit-test`

Body:

```json
{
  "answers": [
    {
      "question": "Apa kelebihan kamu?",
      "selected_option": "Cepat belajar"
    }
  ]
}
```

### `POST /api/intern/apply`

Body:

```json
{
  "job_id": 1
}
```

### `GET /api/intern/applications`

## Company Endpoints

Semua endpoint company membutuhkan:
- bearer token
- role user `company`

### `GET /api/company/profile`
### `POST /api/company/profile/update`
### `GET /api/company/dashboard`
### `GET /api/company/jobs`
### `POST /api/company/jobs`
### `PUT /api/company/jobs/{id}`
### `DELETE /api/company/jobs/{id}`
### `GET /api/company/jobs/{jobId}/applicants`
### `PUT /api/company/applications/{id}/status`
### `GET /api/company/talent/candidates`
### `GET /api/company/talent/candidates/{id}/detail`
### `POST /api/company/talent/candidates/manual`
### `PUT /api/company/talent/candidates/{id}/status`
### `GET /api/company/talent/selected`

Body create job:

```json
{
  "judul_posisi": "Frontend Intern",
  "deskripsi_pekerjaan": "Membangun UI",
  "persyaratan": "React dasar",
  "lokasi": "Surabaya",
  "tipe_magang": "hybrid",
  "gaji_per_bulan": "1500000",
  "status": "ACTIVE"
}
```

## Error Pattern

Sebagian besar endpoint mengembalikan pola:

```json
{
  "status": "error",
  "message": "Penjelasan error"
}
```

Catatan auth company:
- akun `company` hanya bisa login setelah `status_mitra` menjadi `active`
- sebelum itu, frontend sebaiknya tampilkan status "menunggu verifikasi admin"

Namun saat validasi Laravel gagal, response default bisa berbentuk:

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": [
      "The email field is required."
    ]
  }
}
```

## File Penting Untuk Tim Frontend

- Route API: [api.php](/c:/laragon/www/VOCASEEK/vocaseek/routes/api.php)
- Auth controller: [AuthController.php](/c:/laragon/www/VOCASEEK/vocaseek/app/Http/Controllers/Auth/AuthController.php)
- Google OAuth controller: [GoogleController.php](/c:/laragon/www/VOCASEEK/vocaseek/app/Http/Controllers/GoogleController.php)
- Forgot password: [ForgotPasswordController.php](/c:/laragon/www/VOCASEEK/vocaseek/app/Http/Controllers/ForgotPasswordController.php)

## Rekomendasi Handoff

- Berikan file `.env` yang sudah berisi base URL backend yang benar.
- Berikan contoh header bearer token ke frontend.
- Sepakati dulu apakah frontend akan memakai:
  - login email/password via API token, atau
  - login Google via redirect browser
- Jika frontend benar-benar app terpisah, sebaiknya backend ditambah endpoint Google khusus SPA/mobile pada tahap berikutnya.
