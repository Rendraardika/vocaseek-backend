# Vocaseek API README

Dokumen ini dibuat untuk membantu tim frontend memahami endpoint backend Vocaseek dengan cepat tanpa harus membaca controller satu per satu.

## Base URL

- API lokal: `http://localhost:8000/api`
- Frontend lokal saat ini: `http://localhost:5173`

## Format Umum

- Header default:

```http
Accept: application/json
Content-Type: application/json
```

- Untuk memilih bahasa response backend, bisa kirim:

```http
X-Locale: id
```

atau

```http
X-Locale: en
```

- Untuk endpoint yang butuh login:

```http
Authorization: Bearer {token}
Accept: application/json
```

- Respons sukses biasanya berbentuk:

```json
{
  "status": "success",
  "message": "Operasi berhasil"
}
```

- Validasi Laravel biasanya berbentuk:

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

## Alur Auth

### 1. Register

- Endpoint: `POST /api/register`
- Role `intern` memakai JSON.
- Role `company` memakai `multipart/form-data` karena ada `loa_pdf` dan `akta_pdf`.
- Role `company` tidak langsung mendapatkan token login. Akun harus disetujui super admin dulu.

Contoh intern:

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

Contoh response:

```json
{
  "status": "success",
  "message": "Registrasi Berhasil!",
  "token": "plain-text-token",
  "user": "Rendra",
  "role": "intern"
}
```

Contoh response register company:

```json
{
  "status": "success",
  "message": "Registrasi berhasil! Menunggu verifikasi super admin sebelum bisa login.",
  "user": "PIC Company",
  "role": "company"
}
```

### 2. Login

- Endpoint: `POST /api/login`

```json
{
  "email": "rendra@example.com",
  "password": "password123"
}
```

Response:

```json
{
  "status": "success",
  "token": "plain-text-token",
  "role": "intern",
  "user": "Rendra"
}
```

Jika user `company` belum disetujui super admin, login akan ditolak:

```json
{
  "status": "error",
  "message": "Akun company Anda belum disetujui super admin."
}
```

### 3. Ambil user login

- Endpoint: `GET /api/me`

Response:

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

### 4. Logout

- Endpoint: `POST /api/logout`

## Google Login

Ada dua flow:

### Redirect OAuth

1. Frontend arahkan browser ke `GET /api/auth/google`
2. User login di Google
3. Backend handle callback di `GET /api/auth/google/callback`
4. User diarahkan ke web app backend

Flow ini cocok untuk web berbasis redirect.

### Access Token API

- Endpoint: `POST /api/auth/google/token`
- Body:

```json
{
  "access_token": "google-access-token"
}
```

Flow ini lebih cocok untuk SPA/mobile, tetapi frontend harus sudah mendapatkan token dari Google lebih dulu.

## Forgot Password

### Kirim email reset

- Endpoint: `POST /api/forgot-password`

```json
{
  "email": "rendra@example.com"
}
```

### Validasi token reset

- Endpoint: `POST /api/forgot-password/validate-token`

```json
{
  "email": "rendra@example.com",
  "token": "plain-reset-token"
}
```

### Simpan password baru

- Endpoint: `POST /api/reset-password`

```json
{
  "email": "rendra@example.com",
  "token": "plain-reset-token",
  "password": "PasswordBaru123",
  "password_confirmation": "PasswordBaru123"
}
```

## Public Endpoints

- `GET /api/landing-stats`
- `GET /api/popular-vacancies`
- `GET /api/partners`
- `GET /api/test`

## Intern Endpoints

Semua endpoint intern butuh bearer token dan role `intern`.

- `GET /api/intern/profile`
- `GET /api/intern/test/questions`
- `PUT /api/intern/update-profile`
- `POST /api/intern/start-test`
- `POST /api/intern/submit-test`
- `POST /api/intern/apply`

Catatan pre-test:
- soal diambil dari backend, bukan hardcode frontend
- setiap user hanya bisa mengerjakan satu kali
- durasi pre-test saat ini `20` menit

Contoh payload `submit-test`:

```json
{
  "answers": [
    {
      "question_id": 1,
      "selected_option": "Ya"
    }
  ]
}
```

Contoh payload `apply`:

```json
{
  "job_id": 1
}
```

Field upload yang dipakai di `update-profile`:

- `foto`
- `cv_pdf`
- `portofolio_pdf`

Field teks utama:

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

## Company Endpoints

Semua endpoint company butuh bearer token dan role `company`.

- `GET /api/company/profile`
- `POST /api/company/profile/update`
- `GET /api/company/dashboard`
- `GET /api/company/jobs`
- `POST /api/company/jobs`
- `PUT /api/company/jobs/{id}`
- `DELETE /api/company/jobs/{id}`
- `GET /api/company/jobs/{jobId}/applicants`
- `PUT /api/company/applications/{id}/status`

Contoh payload create job:

```json
{
  "judul_posisi": "Frontend Intern",
  "deskripsi_pekerjaan": "Membangun UI React",
  "persyaratan": "Memahami JavaScript dasar",
  "lokasi": "Surabaya",
  "tipe_magang": "hybrid",
  "gaji_per_bulan": "1500000",
  "status": "ACTIVE"
}
```

## Talent Pool Endpoints

Masih di bawah role `company`.

- `GET /api/company/talent/candidates`
- `GET /api/company/talent/candidates/{id}/detail`
- `POST /api/company/talent/candidates/manual`
- `PUT /api/company/talent/candidates/{id}/status`
- `GET /api/company/talent/selected`

Contoh kandidat manual:

```json
{
  "nama": "Budi",
  "email": "budi@example.com",
  "notelp": "08123456789",
  "asal_kampus": "ITS",
  "prodi": "Informatika"
}
```

## Admin Endpoints

Butuh bearer token. Beberapa endpoint hanya boleh diakses `super_admin`, sebagian bisa juga `staff_admin`.

- `GET /api/admin/overview`
- `GET /api/admin/profile`
- `POST /api/admin/profile/update`
- `PUT /api/admin/profile/change-password`
- `GET /api/admin/talents`
- `DELETE /api/admin/talents/{id}`
- `GET /api/admin/partners`
- `POST /api/admin/partners`
- `GET /api/admin/partners/{id}`
- `DELETE /api/admin/partners/{id}`
- `GET /api/admin/verification`
- `PUT /api/admin/verification/{id}/review-status`
- `GET /api/admin/verification/{id}/detail`
- `POST /api/admin/verification/{id}/final`
- `GET /api/admin/users-management`
- `POST /api/admin/users-management`
- `PUT /api/admin/users-management/{id}/status`
- `DELETE /api/admin/users-management/{id}`

## Catatan Integrasi Penting

Ada beberapa hal yang perlu diketahui frontend sejak awal:

- Route `GET /api/intern/applications` ada di `routes/api.php`, tetapi method `getMyApplications()` belum ditemukan di `InternController`. Jadi endpoint ini sebaiknya dianggap belum siap.
- Register `company` sekarang tidak mengembalikan token. Frontend harus menampilkan pesan menunggu approval super admin.
- Login `company` hanya bisa berhasil jika `status_mitra = active`.
- Endpoint verifikasi admin sekarang menerima alias input berikut:
  - `pending`
  - `reviewed`
  - `approve` atau `active`
  - `reject` atau `rejected`
- Status pelamar tidak seragam antar modul. Contoh:
  - Company memakai `PENDING`, `REVIEW`, `INTERVIEW`, `SHORTLISTED`, `ACCEPTED`, `REJECTED`
  - Talent pool memakai `PENDING`, `REVIEWED`, `SHORTLISTED`, `INTERVIEW`, `REJECTED`, `OFFER`
- Beberapa field model juga belum seragam. Contoh:
  - Ada controller yang memakai `judul_posisi`, ada juga yang membaca `judul_pekerjaan`
  - Ada yang memakai `industri`, ada juga `sektor_industri`
- Karena itu, frontend sebaiknya tidak terlalu mengandalkan keseragaman naming sebelum backend dirapikan.

## File Rujukan

- Spesifikasi OpenAPI: [openapi.yaml](/c:/laragon/www/VOCASEEK/vocaseek/docs/openapi.yaml)
- Route API: [api.php](/c:/laragon/www/VOCASEEK/vocaseek/routes/api.php)
- Auth controller: [AuthController.php](/c:/laragon/www/VOCASEEK/vocaseek/app/Http/Controllers/Auth/AuthController.php)
- Google controller: [GoogleController.php](/c:/laragon/www/VOCASEEK/vocaseek/app/Http/Controllers/GoogleController.php)
- Forgot password controller: [ForgotPasswordController.php](/c:/laragon/www/VOCASEEK/vocaseek/app/Http/Controllers/ForgotPasswordController.php)

## Saran Pakai Untuk Frontend

- Jadikan `docs/openapi.yaml` sebagai sumber untuk Swagger UI atau import Postman.
- Jadikan dokumen ini sebagai panduan implementasi harian tim frontend.
- Untuk endpoint yang rawan mismatch field, cek lagi response real dari backend sebelum finalisasi typing TypeScript.
