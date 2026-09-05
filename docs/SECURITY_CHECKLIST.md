# Security Checklist — Sistem ALP DBKL

Status disemak pada **Fasa 7 (Hardening)**. Legenda:
**✅ VERIFIED** = disemak secara aktif fasa ini · **☑ CONFIGURED** = mekanisme wujud (rangka kerja/kod)
tetapi tidak diuji-tembusi · **⚠ ACTION** = perlu tindakan operasi sebelum production.

> Prinsip: **jangan** tandakan PASS tanpa pengesahan. Item production (HTTPS, APP_DEBUG) kekal ⚠ sehingga
> disahkan pada persekitaran production sebenar — lihat `DEPLOYMENT_READINESS.md`.

## Authentication & Session

| Item | Status | Bukti / Nota |
|---|---|---|
| Hashing kata laluan (bcrypt) | ☑ CONFIGURED | `config/hashing`, `User` casts `password` → hashed |
| Login throttling (kelayakan) | ✅ VERIFIED | `LoginRequest` — kunci 5 percubaan per e-mel+IP |
| Login throttling (kadar kasar) | ✅ VERIFIED | Route `throttle:20,1` ditambah Fasa 7 |
| Password reset — had kadar | ✅ VERIFIED | Route `throttle:6,1` (forgot + reset) |
| Password reset — anti-enumerasi | ✅ VERIFIED | Sentiasa pulang mesej sama tanpa dedah e-mel wujud |
| Dasar kata laluan (min 8, huruf+nombor) | ☑ CONFIGURED | Peraturan validasi tukar/set kata laluan |
| Pengguna tidak aktif disekat | ☑ CONFIGURED | Middleware `active` (`EnsureUserIsActive`) |
| Wajib tukar kata laluan | ☑ CONFIGURED | Middleware `password.set` (`EnsurePasswordChanged`) |
| Tiada pendaftaran awam | ✅ VERIFIED | Tiada route register; akaun dicipta oleh admin |

## Authorization

| Item | Status | Bukti / Nota |
|---|---|---|
| RBAC backend (bukan sekadar sembunyi UI) | ✅ VERIFIED | Policy/Gate/`can`; ujian 403 (ALP → dashboard/laporan/eksport) |
| IDOR — projek/perbelanjaan/refund | ✅ VERIFIED | Policy skop `alp_id`; ujian Fasa 5A/6 (403/404 rentas-ALP) |
| System Admin ≠ Finance Operator | ✅ VERIFIED | `ROLE_PERMISSION_MATRIX.md` — tiada kebenaran kewangan operasi |
| Maker ≠ Checker (identiti) | ✅ VERIFIED | Ujian: allocation/adjustment/expense/refund maker tak boleh lulus sendiri |
| Super Admin override diaudit | ☑ CONFIGURED | `SUPER_ADMIN_FINANCIAL_OVERRIDE` |

## Input & Output

| Item | Status | Bukti / Nota |
|---|---|---|
| CSRF | ☑ CONFIGURED | Middleware web default; borang guna `@csrf`; 419 → halaman BM |
| XSS (output escaping) | ✅ VERIFIED | Blade `{{ }}`; satu-satunya `{!! !!}` = SVG ikon hardcoded (tiada input pengguna) |
| Mass assignment | ✅ VERIFIED | Model guna `$fillable`; tiada `->fill($request->all())` / `create($request->all())` |
| Validasi borang (BM) | ☑ CONFIGURED | Form Requests + mesej `lang/ms` |

## File Security

| Item | Status | Bukti / Nota |
|---|---|---|
| Storan peribadi | ✅ VERIFIED | Disk `local` (`storage/app/private`); tiada URL awam |
| Muat turun berkuasa | ✅ VERIFIED | `authorize()` sebelum stream; ujian 403 tanpa kebenaran |
| Nama fail dijana (rawak) | ✅ VERIFIED | `UploadedFile::store()` — nama rawak, bukan nama asal |
| Tiada path traversal | ✅ VERIFIED | Laluan dari DB (`stored_path`), bukan input pengguna |
| Validasi MIME | ✅ VERIFIED | `mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx` |
| Validasi sambungan | ✅ VERIFIED | `extensions:...` (larang boleh-laksana) |
| Had saiz | ✅ VERIFIED | `max:10240` (10 MB) |
| SHA-256 direkod | ✅ VERIFIED | `hash_file('sha256', ...)` semasa simpan |
| Rekod dimuktamadkan immutable | ✅ VERIFIED | Dokumen tak boleh dibuang selepas VERIFIED/CLOSED |

## Export Security

| Item | Status | Bukti / Nota |
|---|---|---|
| Kebenaran eksport | ✅ VERIFIED | `reports.export` diperlukan; ujian 403 |
| URL eksport langsung dilindungi | ✅ VERIFIED | Kawalan di controller (bukan hanya butang UI) |
| Penapis tahun kewangan dihormati | ✅ VERIFIED | Eksport guna tapisan/tahun semasa |
| Nama fail selamat | ✅ VERIFIED | `<slug>-<YYYYMMDD>.xlsx/pdf` (tiada input pengguna mentah) |
| Formula/CSV injection (XLSX) | ✅ VERIFIED | Teks `= + - @` diawali apostrof; ujian `ExportSecurityTest` |
| Wang tidak ditukar ke float | ✅ VERIFIED | Nilai DECIMAL kanonik (Money/BCMath) |
| Tiada kebocoran rahsia (audit) | ✅ VERIFIED | Kunci sensitif ditapis (`AuditReportService`) |

## Financial Integrity

| Item | Status | Bukti / Nota |
|---|---|---|
| Semakan integriti menyeluruh | ✅ VERIFIED | `php artisan integrity:check` → `Exceptions = 0` pada data sihat |
| Ledger immutable (append-only) | ✅ VERIFIED | Model menyekat update/delete `budget_transactions` |
| Idempotensi (unique + kunci baris) | ✅ VERIFIED | Ujian konkurensi/idempotensi Fasa 4/4A/5/5A |

## Error Handling

| Item | Status | Bukti / Nota |
|---|---|---|
| Halaman ralat BM (403/404/419/422/429/500/503) | ✅ VERIFIED | `resources/views/errors/*` ditambah Fasa 7 |
| Tiada stack trace / laluan fail kepada pengguna | ⚠ ACTION | Bergantung `APP_DEBUG=false` di production (lihat Deployment) |

## Production (belum disahkan — persekitaran setempat)

| Item | Status | Nota |
|---|---|---|
| `APP_ENV=production`, `APP_DEBUG=false` | ⚠ ACTION | Wajib sebelum production |
| HTTPS / HSTS | ⚠ ACTION | Konfigurasi pelayan web |
| Kebenaran folder `storage`/`bootstrap/cache` | ⚠ ACTION | Lihat Deployment |
| Backup DB + dokumen peribadi | ⚠ ACTION | Lihat `DEPLOYMENT_READINESS.md` |
