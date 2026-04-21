<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vocaseek API Docs</title>
    <style>
        :root {
            --bg: #f5f7fb;
            --panel: #ffffff;
            --text: #1f2937;
            --muted: #6b7280;
            --line: #e5e7eb;
            --brand: #f59e0b;
            --brand-dark: #1d4ed8;
            --good: #166534;
            --warn-bg: #fff7ed;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background:
                radial-gradient(circle at top left, rgba(29, 78, 216, 0.08), transparent 28%),
                radial-gradient(circle at bottom right, rgba(245, 158, 11, 0.12), transparent 24%),
                var(--bg);
            color: var(--text);
        }

        .wrap {
            max-width: 1120px;
            margin: 0 auto;
            padding: 40px 20px 64px;
        }

        .hero {
            background: linear-gradient(135deg, #ffffff 0%, #eef4ff 52%, #fff8ef 100%);
            border: 1px solid var(--line);
            border-radius: 24px;
            padding: 32px;
            box-shadow: 0 18px 50px rgba(15, 23, 42, 0.08);
        }

        .hero h1 {
            margin: 0 0 10px;
            font-size: 38px;
            line-height: 1.1;
        }

        .hero p {
            margin: 0;
            max-width: 720px;
            color: var(--muted);
            font-size: 16px;
            line-height: 1.7;
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 24px;
        }

        .btn {
            display: inline-block;
            padding: 12px 18px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            border: 1px solid transparent;
        }

        .btn-primary {
            background: var(--brand);
            color: #111827;
        }

        .btn-secondary {
            background: #fff;
            border-color: var(--line);
            color: var(--brand-dark);
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 16px;
            margin-top: 24px;
        }

        .card, .section {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 18px;
            box-shadow: 0 12px 34px rgba(15, 23, 42, 0.05);
        }

        .card {
            padding: 18px 18px 16px;
        }

        .card h3, .section h2 {
            margin: 0 0 10px;
        }

        .card p, .section p, .section li {
            color: var(--muted);
            line-height: 1.65;
        }

        .section {
            margin-top: 22px;
            padding: 24px;
        }

        .section h2 {
            font-size: 22px;
        }

        .code {
            margin: 14px 0 0;
            background: #0f172a;
            color: #e5eefc;
            border-radius: 14px;
            padding: 14px 16px;
            overflow-x: auto;
            font-family: Consolas, Monaco, monospace;
            font-size: 14px;
            line-height: 1.6;
        }

        .endpoint-group {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 16px;
            margin-top: 18px;
        }

        .endpoint-box {
            border: 1px solid var(--line);
            border-radius: 16px;
            padding: 18px;
            background: #fcfdff;
        }

        .endpoint-box h3 {
            margin: 0 0 12px;
            font-size: 17px;
        }

        .endpoint-box ul {
            margin: 0;
            padding-left: 18px;
        }

        .pill {
            display: inline-block;
            margin-bottom: 10px;
            padding: 6px 10px;
            border-radius: 999px;
            background: #e0ecff;
            color: var(--brand-dark);
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.03em;
            text-transform: uppercase;
        }

        .note {
            border-left: 4px solid var(--brand);
            background: var(--warn-bg);
            padding: 14px 16px;
            border-radius: 12px;
            color: #92400e;
            line-height: 1.65;
        }

        .ok {
            color: var(--good);
            font-weight: 600;
        }

        a.inline {
            color: var(--brand-dark);
            font-weight: 600;
            text-decoration: none;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
        }

        th, td {
            text-align: left;
            border-bottom: 1px solid var(--line);
            padding: 12px 10px;
            vertical-align: top;
        }

        th {
            color: #111827;
            font-size: 14px;
        }

        td {
            color: var(--muted);
        }

        @media (max-width: 640px) {
            .hero {
                padding: 24px;
            }

            .hero h1 {
                font-size: 30px;
            }

            .section {
                padding: 18px;
            }
        }
    </style>
</head>
<body>
    <div class="wrap">
        <section class="hero">
            <h1>Vocaseek API Docs</h1>
            <p>
                Halaman ini dibuat supaya tim frontend bisa memahami integrasi backend dengan cepat.
                Untuk penggunaan harian, baca ringkasan di bawah. Untuk tooling seperti Swagger Editor,
                Postman, atau generator client, gunakan file OpenAPI mentah.
            </p>

            <div class="actions">
                <a class="btn btn-primary" href="{{ route('docs.openapi') }}" target="_blank" rel="noopener noreferrer">Buka OpenAPI YAML</a>
                <a class="btn btn-secondary" href="{{ route('docs.swagger') }}">Buka Swagger UI</a>
                <a class="btn btn-secondary" href="https://editor.swagger.io/" target="_blank" rel="noopener noreferrer">Buka Swagger Editor</a>
                <a class="btn btn-secondary" href="{{ route('docs.api-readme') }}" target="_blank" rel="noopener noreferrer">Lihat API README</a>
            </div>
        </section>

        <div class="grid">
            <div class="card">
                <h3>Base URL</h3>
                <p>Gunakan base URL ini untuk request lokal dari frontend.</p>
                <div class="code">http://localhost:8000/api</div>
            </div>

            <div class="card">
                <h3>Auth</h3>
                <p>Mayoritas endpoint private memakai Bearer token dari Sanctum.</p>
                <div class="code">Authorization: Bearer {token}</div>
            </div>

            <div class="card">
                <h3>Content Type</h3>
                <p>Default JSON, tetapi beberapa endpoint upload file harus pakai multipart form.</p>
                <div class="code">Accept: application/json
Content-Type: application/json</div>
            </div>

            <div class="card">
                <h3>Status</h3>
                <p><span class="ok">Dokumentasi aktif</span>. OpenAPI dan README sudah disesuaikan dengan route backend saat ini.</p>
            </div>
        </div>

        <section class="section">
            <h2>Alur Cepat Frontend</h2>
            <table>
                <thead>
                    <tr>
                        <th>Flow</th>
                        <th>Endpoint</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Login email/password</td>
                        <td><code>POST /api/login</code></td>
                        <td>Simpan token dari response lalu kirim sebagai Bearer token. Untuk role company, login hanya bisa jika sudah di-approve super admin.</td>
                    </tr>
                    <tr>
                        <td>Ambil user login</td>
                        <td><code>GET /api/me</code></td>
                        <td>Dipakai untuk cek role dan data user aktif.</td>
                    </tr>
                    <tr>
                        <td>Forgot password</td>
                        <td><code>POST /api/forgot-password</code></td>
                        <td>Reset link mengarah ke frontend URL yang ada di <code>.env</code>.</td>
                    </tr>
                    <tr>
                        <td>Google login redirect</td>
                        <td><code>GET /api/auth/google</code></td>
                        <td>Cocok untuk flow browser redirect.</td>
                    </tr>
                    <tr>
                        <td>Google token login</td>
                        <td><code>POST /api/auth/google/token</code></td>
                        <td>Cocok untuk SPA/mobile jika access token Google sudah tersedia.</td>
                    </tr>
                </tbody>
            </table>
        </section>

        <section class="section">
            <h2>Kelompok Endpoint</h2>
            <div class="endpoint-group">
                <div class="endpoint-box">
                    <span class="pill">Public</span>
                    <h3>Endpoint Umum</h3>
                    <ul>
                        <li><code>GET /api/landing-stats</code></li>
                        <li><code>GET /api/popular-vacancies</code></li>
                        <li><code>GET /api/test</code></li>
                    </ul>
                </div>

                <div class="endpoint-box">
                    <span class="pill">Auth</span>
                    <h3>Login dan Akun</h3>
                    <ul>
                        <li><code>POST /api/register</code></li>
                        <li><code>POST /api/login</code></li>
                        <li><code>GET /api/me</code></li>
                        <li><code>POST /api/logout</code></li>
                        <li><code>POST /api/forgot-password</code></li>
                        <li><code>POST /api/reset-password</code></li>
                    </ul>
                </div>

                <div class="endpoint-box">
                    <span class="pill">Intern</span>
                    <h3>Pelamar</h3>
                    <ul>
                        <li><code>GET /api/intern/profile</code></li>
                        <li><code>PUT /api/intern/update-profile</code></li>
                        <li><code>POST /api/intern/start-test</code></li>
                        <li><code>POST /api/intern/submit-test</code></li>
                        <li><code>POST /api/intern/apply</code></li>
                    </ul>
                </div>

                <div class="endpoint-box">
                    <span class="pill">Company</span>
                    <h3>Mitra dan Lowongan</h3>
                    <ul>
                        <li><code>GET /api/company/profile</code></li>
                        <li><code>POST /api/company/profile/update</code></li>
                        <li><code>GET /api/company/dashboard</code></li>
                        <li><code>GET /api/company/jobs</code></li>
                        <li><code>POST /api/company/jobs</code></li>
                        <li><code>PUT /api/company/jobs/{id}</code></li>
                    </ul>
                </div>

                <div class="endpoint-box">
                    <span class="pill">Talent Pool</span>
                    <h3>Kandidat Perusahaan</h3>
                    <ul>
                        <li><code>GET /api/company/talent/candidates</code></li>
                        <li><code>GET /api/company/talent/candidates/{id}/detail</code></li>
                        <li><code>POST /api/company/talent/candidates/manual</code></li>
                        <li><code>PUT /api/company/talent/candidates/{id}/status</code></li>
                        <li><code>GET /api/company/talent/selected</code></li>
                    </ul>
                </div>

                <div class="endpoint-box">
                    <span class="pill">Admin</span>
                    <h3>Operasional Admin</h3>
                    <ul>
                        <li><code>GET /api/admin/overview</code></li>
                        <li><code>GET /api/admin/partners</code></li>
                        <li><code>GET /api/admin/talents</code></li>
                        <li><code>GET /api/admin/users-management</code></li>
                        <li><code>GET /api/admin/verification</code></li>
                    </ul>
                </div>
            </div>
        </section>

        <section class="section">
            <h2>Contoh Request</h2>
            <p>Contoh login dasar yang paling sering dipakai frontend:</p>
            <div class="code">POST /api/login
{
  "email": "rendra@example.com",
  "password": "password123"
}</div>

            <p style="margin-top: 18px;">Contoh response sukses:</p>
            <div class="code">{
  "status": "success",
  "token": "plain-text-token",
  "role": "intern",
  "user": "Rendra"
}</div>
        </section>

        <section class="section">
            <h2>Catatan Integrasi</h2>
            <div class="note">
                Ada beberapa endpoint lama yang belum sepenuhnya konsisten di backend. Contohnya, beberapa modul memakai nama field atau status yang berbeda seperti
                <code>judul_posisi</code> vs <code>judul_pekerjaan</code>, dan <code>REVIEW</code> vs <code>REVIEWED</code>.
                Karena itu, frontend sebaiknya cek response aktual sebelum mengunci typing yang terlalu ketat.
            </div>

            <ul>
                <li>Register `company` tidak lagi mengembalikan token login. Status awalnya menunggu verifikasi super admin.</li>
                <li>Login `company` akan ditolak sampai status mitra menjadi <code>active</code>.</li>
                <li><code>GET /api/intern/applications</code> sudah ada di route, tetapi method controller belum ditemukan saat dokumentasi ini disusun.</li>
                <li>Upload file wajib memakai <code>multipart/form-data</code>, terutama register company dan update profile.</li>
                <li>Reset password mengandalkan mail server backend. Jika email gagal terkirim, cek konfigurasi <code>MAIL_*</code> di backend.</li>
            </ul>
        </section>

        <section class="section">
            <h2>File Dokumentasi</h2>
            <ul>
                <li><a class="inline" href="{{ route('docs.openapi') }}" target="_blank" rel="noopener noreferrer">Raw OpenAPI YAML</a></li>
                <li><a class="inline" href="{{ route('docs.swagger') }}">Swagger UI</a></li>
                <li><a class="inline" href="{{ route('docs.api-readme') }}" target="_blank" rel="noopener noreferrer">API README</a></li>
                <li><a class="inline" href="{{ route('docs.frontend-handoff') }}" target="_blank" rel="noopener noreferrer">Frontend handoff lama</a></li>
            </ul>
        </section>
    </div>
</body>
</html>
