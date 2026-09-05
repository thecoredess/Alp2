# Deployment Readiness — Sistem ALP DBKL

**Dokumentasi sahaja.** Panduan ini menyediakan langkah untuk deployment production yang terkawal.
**JANGAN deploy** tanpa kelulusan rasmi dan backup lengkap. Sistem kini berstatus **UAT READY**,
bukan *production ready* (deployment production belum disahkan).

## 1. Keperluan persekitaran

| Komponen | Versi minimum | Nota |
|---|---|---|
| PHP | **8.3+** | Diuji pada 8.3.31 |
| Laravel | **12** | — |
| MySQL | **8.x** (diuji 8.4) | Ketepatan `DECIMAL(15,2)` + `SUM()` |
| Composer | 2.x | Pemasangan dependency PHP |
| Node.js + npm | LTS semasa | Bina aset frontend (Vite) |
| Pelayan web | Nginx/Apache | Dengan HTTPS |

**Sambungan PHP diperlukan:** `pdo_mysql`, `mbstring`, `openssl`, `bcmath` (WAJIB — aritmetik wang tepat),
`ctype`, `json`, `tokenizer`, `xml`, `fileinfo`, `zip` (WAJIB — penjana eksport XLSX), `gd` (imej).

## 2. Konfigurasi `.env` (production)

```
APP_NAME="Sistem ALP DBKL"
APP_ENV=production
APP_DEBUG=false            # WAJIB false — elak dedah stack trace/rahsia
APP_KEY=                   # php artisan key:generate
APP_URL=https://<domain>

APP_LOCALE=ms
APP_FALLBACK_LOCALE=en

DB_CONNECTION=mysql
DB_HOST=<host>
DB_PORT=3306
DB_DATABASE=alp_dbkl
DB_USERNAME=<user>
DB_PASSWORD=<secret>       # JANGAN commit

SESSION_DRIVER=database    # atau redis jika tersedia
SESSION_SECURE_COOKIE=true # kuki hanya melalui HTTPS
QUEUE_CONNECTION=database
MAIL_MAILER=smtp           # konfigurasi SMTP sebenar untuk reset kata laluan
FILESYSTEM_DISK=local
```

> `.env` diabaikan oleh Git. Simpan rahsia dalam pengurus rahsia / pembolehubah persekitaran pelayan.

## 3. Prosedur pemasangan

```bash
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan key:generate            # sekali sahaja jika APP_KEY kosong
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 4. Migrasi pangkalan data (production)

**BACKUP DAHULU** (lihat §7). Kemudian:

```bash
php artisan migrate --force         # --force diperlukan dalam production
```

- **JANGAN** jalankan `migrate:fresh` / `migrate:refresh` di production (memadam data).
- **JANGAN** jalankan `DevSeeder` di production — ia **data pembangunan** (akaun ujian, kata laluan `password`).
  `DevSeeder` sudah melindungi diri (`if app()->environment('production') return`), namun jangan panggil manual.
- Seed yang **selamat-production** sahaja jika perlu: `RolePermissionSeeder`, `DocumentRequirementSeeder`,
  `ProjectDocumentRequirementSeeder`, `WorkflowSettingsSeeder`, `ApprovalLevelSeeder` (idempotent).

```bash
php artisan db:seed --class=RolePermissionSeeder --force
php artisan db:seed --class=DocumentRequirementSeeder --force
php artisan db:seed --class=ProjectDocumentRequirementSeeder --force
php artisan db:seed --class=WorkflowSettingsSeeder --force
```

> **AMARAN SEED PRODUCTION:** ambang matriks kelulusan & kewajipan dokumen yang di-seed adalah
> **cadangan pembangunan**, bukan polisi rasmi DBKL. Sahkan/laraskan dengan pihak berkuasa sebelum go-live.

## 5. Kebenaran fail & storan

```bash
chmod -R ug+rwx storage bootstrap/cache
# Pemilik = pengguna pelayan web (cth: www-data)
```

- `storage/app/private` — dokumen sulit (bukti perbelanjaan/penutupan/refund). **Mesti** di luar akar web awam.
- Pastikan `public/` sahaja terdedah oleh pelayan web; **jangan** dedahkan `storage/app`.

## 6. HTTPS & pengukuhan pelayan

- Kuatkuasa HTTPS + HSTS.
- `SESSION_SECURE_COOKIE=true`, kuki `HttpOnly` + `SameSite=Lax`.
- Had saiz muat naik pelayan web ≥ 10 MB (sepadan had aplikasi).
- Pertimbang WAF / had kadar peringkat pelayan sebagai pelengkap kepada had kadar aplikasi.

## 7. Backup / Restore (keperluan minimum)

Lihat juga: bahagian di bawah diringkaskan dalam senarai semak go-live.

| Perkara | Keperluan minimum |
|---|---|
| **Backup pangkalan data** | `mysqldump` penuh **sebelum setiap migrasi** + backup berjadual harian |
| **Backup dokumen peribadi** | Arkib `storage/app/private` (bukti kewangan) selari dengan backup DB |
| **Ujian restore** | Sahkan restore DB + dokumen ke persekitaran berasingan **sebelum** bergantung padanya |
| **Retensi** | Cadangan: harian 7 hari, mingguan 4 minggu, bulanan 12 bulan (laraskan ikut polisi DBKL) |

Contoh backup:

```bash
# Pangkalan data
mysqldump --single-transaction --routines --triggers alp_dbkl > alp_dbkl_$(date +%F).sql
# Dokumen peribadi
tar czf alp_docs_$(date +%F).tar.gz storage/app/private
```

Contoh restore (ke persekitaran ujian):

```bash
mysql alp_dbkl_restore_test < alp_dbkl_YYYY-MM-DD.sql
tar xzf alp_docs_YYYY-MM-DD.tar.gz -C /path/to/restore
```

> **JANGAN** konfigur backup awan sekarang — ini fasa persediaan setempat. Dokumentasikan sahaja.

## 7b. Pertimbangan prestasi (skala)

Semasa UAT (isipadu data pembangunan) semua skrin membuka serta-merta. Pertimbangan untuk skala besar
(**bukan masalah UAT semasa**; tiada tindakan diperlukan untuk go-live UAT):

- **Dashboard Eksekutif** mengira penunjuk integriti (rekonsiliasi + kualiti data) yang mengulang setiap
  projek — bertaraf `O(projek)` (≈42 pertanyaan untuk 4 projek). Untuk ratusan projek setahun, pertimbang
  **cache jangka pendek** untuk penunjuk integriti, atau alihkan `integrity:check` ke jadual (cron) dan
  paparkan hasil tersimpan.
- **Laporan Ledger** memuat semua transaksi setahun untuk baki berjalan (tidak berhalaman kerana baki
  berjalan memerlukan jujukan penuh). Untuk isipadu tinggi, pertimbang penapisan julat tarikh (sudah ada)
  atau eksport-sahaja untuk set besar.
- **Laporan Projek/CSR** mengira ringkasan kewangan per projek dari ledger (mengekalkan sumber kebenaran) —
  pertimbang paginasi jika senarai menjadi sangat besar.

> Elakkan pengoptimuman pramatang yang mengubah pengiraan kewangan diluluskan. Tiada Redis/infrastruktur baharu
> diperlukan untuk UAT.

## 8. Pertimbangan rollback

- Backup DB + dokumen **sebelum** migrasi membolehkan restore penuh jika migrasi gagal.
- Ledger bersifat **append-only & immutable** — pembetulan data dibuat melalui transaksi baharu
  (pelarasan/pembalikan/refund yang diluluskan), **bukan** `UPDATE`/`DELETE` manual.
- Untuk perubahan kod: simpan artifak versi sebelumnya; `migrate:rollback` hanya jika migrasi menyediakan
  `down()` yang selamat dan **tiada data hilang** — sahkan dahulu.

## 9. Semakan sebelum go-live (ringkas)

- [ ] `APP_ENV=production`, `APP_DEBUG=false`
- [ ] `APP_KEY` dijana; rahsia tidak di-commit
- [ ] HTTPS + kuki selamat
- [ ] `bcmath` & `zip` dimuatkan
- [ ] `php artisan migrate --force` selepas backup
- [ ] Hanya seeder selamat-production dijalankan (bukan `DevSeeder`)
- [ ] Ambang matriks kelulusan & kewajipan dokumen disahkan pihak berkuasa
- [ ] `php artisan integrity:check` → `Financial Integrity Exceptions = 0`
- [ ] Backup DB + dokumen berjadual & **restore diuji**
- [ ] Kebenaran `storage`/`bootstrap/cache` betul; `storage/app` tidak terdedah
- [ ] `config:cache`, `route:cache`, `view:cache` dijalankan
- [ ] SMTP sebenar untuk e-mel reset kata laluan
