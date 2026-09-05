# Sistem Ahli Lembaga Penasihat (ALP) DBKL

Sistem Dewan Bandaraya Kuala Lumpur (DBKL) untuk mengurus **sumbangan ALP** mengikut **URS v1.2**
(permohonan → semakan Pegawai JP → Peraku/PEPU → komitmen → baucar/JKEW → report card),
berserta asas ledger peruntukan yang dikongsi.

> **SSOT:** **URS v1.2** (bukan Hybrid). Lihat `docs/PANDUAN_ANALISIS_URS_v1.2.md`, `docs/UAT_CHECKLIST.md`, `docs/PANDUAN_LOGIN.md`.
>
> **Status kod:** Realignment Fasa 6–10 + residual (JKEW, checklist JP, recipients, M10/M11) **siap**.
> Seterusnya: **sesi UAT manusia** — `docs/UAT_CHECKLIST.md`. PHPUnit aktif ~182 ujian.
>
> **URL setempat:** http://localhost/alp/public/ · akaun DevSeeder · kata laluan `password`
>
> **PENTING:** *Pending Request* (permohonan dihantar belum lulus) **BUKAN** komitmen ledger.
> Hanya kelulusan akhir mencipta `COMMITMENT`. Modul projek/belanja = **legacy** (tiada route aktif).
>
> **Ambang matriks kelulusan yang diseed adalah DATA PEMBANGUNAN, bukan polisi rasmi DBKL.**

---

## Project Overview

Matlamat sistem: memastikan proses peruntukan & kelulusan projek ALP dibuat secara **telus,
boleh dijejak, boleh diaudit, selamat, dan berasaskan baki bajet sebenar**.

Modul penuh (mengikut fasa): Pengguna & Peranan · ALP · Tahun Kewangan · Peruntukan & Ledger ·
Permohonan · Semakan · Kelulusan · Projek · Perbelanjaan · Laporan · Audit Trail · Notifikasi.

## Architecture

Server-rendered Laravel (bukan SPA). Lapisan:

- **Persembahan** — Blade + Tailwind CSS v4 + Alpine.js (interaksi ringan).
- **Aplikasi** — Controllers nipis → Form Requests (validasi BM) → Service layer (logik bajet/workflow/projek/laporan) → Policies/Gates.
- **Domain** — Eloquent Models, Enums, Support classes (cth: `App\Support\Permissions`).
- **Data** — MySQL 8, migrations dengan FK/index/unique constraint.

Prinsip teras: bajet melalui **ledger append-only**, RBAC **backend** (Policy/Gate, bukan hide/show butang),
**audit trail** penuh, dan **state machine** terkawal untuk workflow (permohonan, bajet, projek).

## Tech Stack

| Komponen | Versi |
|---|---|
| PHP | 8.3+ |
| Laravel | 12 |
| MySQL | 8.4 |
| Tailwind CSS | 4 (via Vite) |
| Alpine.js | 3 |
| RBAC | spatie/laravel-permission |
| Ujian | PHPUnit |

## Installation

```bash
# 1. Dependency
composer install
npm install

# 2. Persekitaran
cp .env.example .env
php artisan key:generate
# Kemas kini DB_* dalam .env (lihat Database di bawah)

# 3. Pangkalan data
php artisan migrate --seed        # migrate + RolePermissionSeeder + DevSeeder

# 4. Aset frontend
npm run build                     # atau: npm run dev

# 5. Jalankan
php artisan serve --port=8123
```

## Environment

Kunci penting dalam `.env`:

```
APP_NAME="Sistem ALP DBKL"
APP_LOCALE=ms
APP_FALLBACK_LOCALE=en
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=alp_dbkl
DB_USERNAME=alp_user
DB_PASSWORD=********      # jangan commit
```

> `.env` sengaja diabaikan oleh Git. Jangan sekali-kali commit kata laluan / secret.

## Database

Jadual: `users` (dilanjutkan), `alps`, `financial_years`, Spatie (`roles`, dsb); bajet:
`allocations`, `budget_transactions` (ledger immutable), `audit_logs`; permohonan (Fasa 3):
`applications`, `application_budget_items`, `application_documents`, `application_status_histories`,
`application_sequences` (pembilang nombor), `document_requirements` (kewajipan boleh dikonfig);
semakan/kelulusan (Fasa 4): `application_reviews`, `approval_levels`, `application_approvals`,
`application_revisions`, `application_workflow_settings`; governance bajet (Fasa 4A): `budget_requests`,
`budget_request_histories`; `budget_transactions` dilanjutkan dengan `application_id` + unique(`application_id`,`type`)
dan `budget_request_id` + unique(`budget_request_id`); projek (Fasa 5): `projects` (unique `application_id`),
`project_sequences`, `project_milestones`, `project_progress_histories`, `project_status_histories`,
`project_expenses`, `project_expense_histories`, `project_reports`, `project_documents`;
bukti, refund & penutupan (Fasa 5A): `project_expense_refunds`, `project_expense_refund_histories`,
`project_document_requirements` (kewajipan dokumen penutupan boleh dikonfig);
`budget_transactions` dilanjutkan dengan `project_id` + `project_expense_id` + unique(`project_expense_id`)
+ `project_expense_refund_id` + unique(`project_expense_refund_id`); `project_documents` dilanjutkan dengan
`project_expense_refund_id` (bukti refund) + `category` (expense/closure/refund evidence).
Fasa 6 (Laporan) tidak menambah jadual — semua nilai dikira dari ledger sedia ada.

## Roles

9 peranan (lihat `App\Enums\RoleName`):
`super_admin`, `system_admin`, `alp`, `urussetia_alp`, `pegawai_urussetia`,
`pegawai_kewangan`, `pegawai_teknikal`, `pelulus`, `pengurusan`.

- **super_admin** melepasi semua semakan melalui `Gate::before`.
- Kebenaran dipetakan dalam `App\Support\Permissions`.

## Workflow

Aliran permohonan penuh (Draf → Hantar → Semakan Urus Setia → Kewangan → Teknikal →
Kelulusan → Committed → Pelaksanaan → Laporan → Selesai/Ditutup). **Keseluruhan aliran ini kini
dilaksanakan (Fasa 3–6):** permohonan (Fasa 3), semakan/kelulusan/komitmen (Fasa 4/4A), projek &
perbelanjaan (Fasa 5), bukti & refund (Fasa 5A), dashboard & laporan (Fasa 6).

**Permohonan (Fasa 3):** wizard 6-langkah (Maklumat → Objektif & Skop → Pecahan Bajet → Dokumen →
Semakan → Hantar). Draf boleh disimpan & disambung semula. Penghantaran adalah atomik melalui
`ApplicationSubmissionService` (kunci baris allocation untuk keselamatan serentak): mengesahkan
draf, tahun kewangan terbuka, kira semula jumlah tepat, dokumen wajib lengkap, dan baki mencukupi
(termasuk *Pending Request* lain) sebelum menetapkan snapshot `requested_amount`, status `SUBMITTED`,
sejarah status, dan rekod audit. **Selepas SUBMITTED, permohonan menjadi baca-sahaja.**

- **Nombor permohonan:** dijana melalui jadual pembilang (`application_sequences`) dengan `lockForUpdate`
  (bukan `COUNT(*)+1`), format `ALP/CSR/2026/0001`. Dijana semasa draf dicipta.
- **Item bajet:** kuantiti **integer** (Fasa 3), `unit_cost` & `total` `DECIMAL(15,2)`; `total = kuantiti × unit_cost`
  dikira backend menggunakan `Money` (tepat). Frontend tidak dipercayai.
- **Dokumen:** disimpan pada storan **peribadi** (`storage/app/private`), muat turun melalui kebenaran
  Laravel (tiada URL awam). Sekatan jenis/saiz; SHA-256 direkod. Kewajipan dokumen boleh dikonfig
  (`document_requirements`).

## Pending Request vs Committed

| Konsep | Maksud | Dalam ledger? |
|---|---|---|
| **Ledger Available** | Allocation − Committed − Spent | Ya (sumber kebenaran) |
| **Pending Request** | Σ `requested_amount` permohonan berstatus pending (belum lulus/tolak/batal) | **TIDAK** |
| **Projected Available** | Ledger Available − Pending Request | Dikira |

Semakan penghantaran: `jumlah baharu ≤ Ledger Available − Pending Request lain`.

## Budget Logic

**Ledger ialah satu-satunya sumber kebenaran kewangan** (`budget_transactions`) — tiada lajur baki
yang boleh diedit secara bebas. Semua nilai dikira oleh `App\Services\Budget\BudgetService` dengan
menjumlahkan transaksi.

**Ketepatan wang (exact decimal):** Semua aritmetik wang menggunakan objek nilai `App\Support\Money`
(berasaskan **BCMath**, string desimal skala 2) — **tiada float** untuk penambahan, penolakan,
perbandingan, jumlah, atau baki. DB kekal `DECIMAL(15,2)`; `SUM()` MySQL adalah tepat. `Money::of()`
menormalkan input (`100`, `100.0`, `100.00` → `"100.00"`) dan menolak NaN/Infinity/notasi saintifik/
>2 desimal/luar julat. Hanya peratus penggunaan (nisbah paparan, bukan wang) dipulangkan sebagai float.

- **Jadual:** `allocations` (header akaun per ALP×tahun, tanpa jumlah), `budget_transactions`
  (append-only, immutable — model menyekat update/delete).
- **Jenis transaksi** (`BudgetTransactionType`): `INITIAL_ALLOCATION`, `ALLOCATION_ADJUSTMENT`,
  `COMMITMENT`, `COMMITMENT_REVERSAL`, `COMMITMENT_RELEASE`, `EXPENDITURE`, `REFUND`, `PROJECT_CLOSURE_ADJUSTMENT`.
- **Formula** (kesan baldi rasmi — `BudgetTransactionType::deltas`):
  - `Allocation = Σ (initial + adjustment + closure)`
  - `Committed = Σ COMMITMENT − COMMITMENT_REVERSAL − COMMITMENT_RELEASE − EXPENDITURE + REFUND`
    (perbelanjaan mengurangkan komitmen → tiada double-count; refund memulihkan komitmen)
  - `Spent (bersih) = Σ EXPENDITURE − REFUND`
  - `Available = Allocation − Committed − Spent`
- **Kawalan:** tiada transaksi dalam tahun kewangan CLOSED; hanya satu peruntukan awal per ALP×tahun
  (selebihnya = pelarasan); pelarasan tidak boleh menurunkan peruntukan di bawah (committed + spent);
  operasi atomik (DB transaction) & diaudit.
- Fasa 2 *mencipta* `INITIAL_ALLOCATION` & `ALLOCATION_ADJUSTMENT`; `COMMITMENT`/`COMMITMENT_REVERSAL`
  digunakan pada Fasa 4/4A, dan `EXPENDITURE`/`COMMITMENT_RELEASE`/`REFUND` pada Fasa 5/5A.

## Project Management & Actual Expenditure (Fasa 5)

Selepas kelulusan akhir, satu **projek** dijana secara automatik & atomik (dalam transaksi kelulusan;
`unique(application_id)` menjamin **1 permohonan diluluskan = 1 projek**). Nombor projek `PRJ/CSR/2026/0001`
(pembilang berkunci, bukan COUNT(*)+1). `approved_amount` ialah snapshot tidak boleh ubah.

- **Kitaran hayat** (`ProjectService`): `NOT_STARTED → IN_PROGRESS → COMPLETED → CLOSED` (+ `DELAYED`, `CANCELLED`);
  peralihan dikawal + sejarah append-only (`project_status_histories`). Milestone (`project_milestones`) &
  kemajuan 0–100 (`project_progress_histories`, append-only). Projek CLOSED/CANCELLED tidak boleh diubah.
- **Perbelanjaan sebenar — maker-checker** (`project_expenses`): `DRAFT → PENDING_VERIFICATION → VERIFIED`
  (cabang REJECTED/REVISION_REQUIRED). **Tiada kesan ledger sehingga VERIFIED.** Pengesahan
  (`ProjectExpenseVerificationService`) — satu transaksi atomik: kunci expense + projek + allocation, sahkan
  maker≠checker, projek bukan CLOSED, jumlah ≤ **baki komitmen projek**, belum diposkan; tandakan VERIFIED &
  poskan **satu** `EXPENDITURE` (dikaitkan `project_id`+`project_expense_id`, unique). Idempoten & rollback penuh.
- **Penggunaan komitmen:** `EXPENDITURE` mengurangkan baki committed & menambah spent (tiada double-count).
  Contoh: Komitmen 85,000 → belanja 20,000 + 30,000 → Baki Komitmen 35,000, Spent 50,000.
- **Ringkasan kewangan** (`ProjectFinancialService`) dikira **dari ledger** (skop `project_id`), bukan jumlah
  `project_expenses`. Silang-projek dilindungi (perbelanjaan Projek A tidak boleh guna komitmen Projek B).
- **Penyiapan & penutupan:** COMPLETED perlukan progress 100% + milestone selesai. CLOSED
  (`ProjectClosureService`, atomik) perlukan COMPLETED + laporan akhir + tiada perbelanjaan belum selesai;
  ia melepaskan baki komitmen tidak diguna melalui `COMMITMENT_RELEASE` (tiada transaksi jika baki = 0), lalu
  mengesahkan baki komitmen = 0. Penutupan berganda tidak menghasilkan pelepasan berganda.
- **Laporan akhir** (`project_reports`): ringkasan/outcome; CSR (bilangan penerima manfaat, impak),
  Development (ringkasan penyiapan).
- **Peranan:** Urus Setia DBKL = operator projek (start/kemajuan/milestone/complete/laporan); Pegawai Kewangan =
  **maker** perbelanjaan; Pelulus = **checker** perbelanjaan + penutupan. ALP lihat projek sendiri sahaja.
- Ledger kekal immutable; pembetulan melalui transaksi baharu (release/refund), bukan edit.

## Financial Evidence, Refund & Closure Evidence (Fasa 5A)

Melengkapkan kitaran bukti kewangan & pemulihan dana. Prinsip teras: **jangan** padam/sunting
perbelanjaan DISAHKAN atau tulis semula transaksi ledger lama — jika dana dipulihkan, **cipta
transaksi REFUND baharu**.

- **Bukti dokumen (storan peribadi)** (`ProjectDocumentService`): bukti perbelanjaan, bukti penutupan,
  dan bukti refund. Nama fail dijana, SHA-256 direkod, sekatan MIME/sambungan/saiz. Perbelanjaan **mesti**
  ada ≥1 bukti sebelum dihantar; dokumen menjadi immutable selepas dimuktamadkan (VERIFIED/CLOSED).
- **Refund / pemulihan dana — maker-checker** (`project_expense_refunds`): `DRAFT →
  PENDING_VERIFICATION → VERIFIED` (cabang REJECTED/REVISION_REQUIRED). Hanya terhadap perbelanjaan
  **VERIFIED**; refund terkumpul ≤ jumlah perbelanjaan; disekat jika projek **CLOSED** (tiada pembukaan
  semula senyap). Pengesahan (`ProjectRefundVerificationService`) memposkan **satu** transaksi `REFUND`
  atomik (unique `project_expense_refund_id`), maker≠checker, idempoten. REFUND membalikkan EXPENDITURE:
  `spent` turun, `committed` (baki komitmen projek) dipulihkan.
- **Bukti penutupan wajib** (`project_document_requirements`, boleh dikonfig ikut jenis projek):
  penutupan projek disekat sehingga semua dokumen wajib (mis. Laporan Akhir + Gambar Penyiapan untuk CSR;
  + Sijil Penyiapan untuk Development) dimuat naik.
- **Rekonsiliasi selepas refund** (`ProjectFinancialService`): dedah Gross Spent / Refunded / **Net Spent
  = Gross − Refund**; invarian `Diluluskan = Baki Komitmen + Net Spent + Dilepaskan` kekal sah.
- **Peranan:** Pegawai Kewangan = **maker** refund + muat naik bukti perbelanjaan; Pelulus = **checker**
  refund; Urus Setia = bukti penutupan. Audit merekod setiap muat naik/buang & peralihan refund.

## Executive Dashboard & Reporting (Fasa 6)

Lapisan **visibiliti pengurusan & pelaporan** di atas data yang telah diluluskan. **Tiada sumber
kebenaran kewangan baharu** — setiap nilai rasmi dikira dari `budget_transactions` (ledger) melalui
perkhidmatan pelaporan yang menggunakan semula kesan baldi rasmi (`BudgetTransactionType::deltas`),
dengan aritmetik wang tepat (Money/BCMath, tiada float).

- **Teras kewangan** (`FinancialBreakdown` + `FinancialReportService`): satu pengiraan tunggal bagi
  Allocation, Committed, Gross Spent, Refunded, **Net Spent = Gross − Refund**, Released, Available,
  Pending (permohonan menunggu) & Projected Available. **Pending ≠ Committed; Gross ≠ Net.** Guna
  agregasi pangkalan data (GROUP BY + SUM) untuk elak N+1.
- **Dashboard Eksekutif** (`dashboard.executive`): KPI menyeluruh + kad operasi (ALP, permohonan/projek
  aktif, lewat, selesai, ditutup) + carta penggunaan ALP (SVG ringan, tiada pustaka) + trend belanja
  bersih bulanan + status integriti (rekonsiliasi & kualiti data). **Dashboard Kewangan**
  (`dashboard.finance`): giliran menunggu (peruntukan/pelarasan/perbelanjaan/refund) + ringkasan.
- **Penapis tahun kewangan global** (`?fy=`) pada setiap dashboard/laporan; lalai = tahun aktif; tidak
  mencampurkan tahun secara senyap.
- **Laporan** (`/laporan`) — Kewangan (Peruntukan mengikut ALP, Ledger dengan baki berjalan, Rekonsiliasi,
  Maker-Checker, Kualiti Data), Permohonan (status/amaun/corong), Projek (register + ringkasan kewangan +
  senarai *Perlu Perhatian* dikira dari keadaan sebenar), CSR (impak, penerima manfaat — null dikendalikan
  selamat), Audit (jejak, nilai sensitif ditapis). Setiap laporan: penapis, kad ringkasan, jadual,
  **drill-down** dan butang eksport.
- **Rekonsiliasi & Kualiti Data (kawalan dalaman):** *Reconciliation Exceptions* (projek di mana
  `Diluluskan ≠ Baki Komitmen + Net Spent + Released`) dan enam semakan kualiti data (mis. perbelanjaan/refund
  disahkan tanpa transaksi ledger, projek ditutup dengan baki komitmen). **Lapor sahaja — tiada pembaikan
  automatik.** Sistem sihat = 0.
- **Eksport XLSX & PDF tulen** (`XlsxWriter` via ZipArchive; `PdfWriter` — tiada pakej luar): nilai wang
  sebagai string DECIMAL kanonik (tiada ralat float), kepala surat DBKL, tajuk, penapis & tarikh jana.
  Laporan lebar → PDF landskap. **Laluan eksport dilindungi** — memerlukan `reports.export`; pengguna tanpa
  kebenaran laporan tidak boleh memanggil URL eksport.
- **Kebenaran (RBAC):** `dashboard.executive`, `dashboard.finance`, `reports.view`, `reports.financial`,
  `reports.applications`, `reports.projects`, `reports.csr`, `reports.audit`, `reports.export`. Pengurusan =
  penuh; Kewangan/Pelulus = kewangan+projek; Urus Setia = permohonan/projek/CSR; Teknikal = projek; ALP =
  skop-sendiri. Skop ALP dikuatkuasa di backend (bukan sekadar sembunyi butang).

## Hardening & UAT Readiness (Fasa 7)

Pengukuhan keselamatan, integriti & kesediaan UAT — **tiada ciri perniagaan baharu**, tiada perubahan
semantik ledger. Ringkasan:

- **Had kadar (rate limit):** log masuk (`throttle:20,1` + kunci 5-percubaan berasaskan kelayakan),
  reset kata laluan (`throttle:6,1`), dan laporan/eksport (`throttle:60,1` — murah hati untuk staf normal).
- **Halaman ralat BM** (`resources/views/errors/`): 403/404/419/422/429/500/503 — tiada stack trace,
  laluan fail, atau rahsia didedah (bergantung `APP_DEBUG=false` di production).
- **Perlindungan eksport:** XLSX menneutralkan suntikan formula (teks bermula `= + - @` diawali apostrof;
  ujian `ExportSecurityTest`); wang kekal DECIMAL kanonik (bukan float); URL eksport dilindungi kebenaran
  (`reports.export`); nilai sensitif audit ditapis.
- **Semakan integriti kewangan:** `php artisan integrity:check` — menggabungkan semakan hala-depan
  (sumber → ledger) dengan hala-balik (ledger → sumber: COMMITMENT/EXPENDITURE/REFUND tanpa rekod sumber
  disahkan) + duplikasi + pelanggaran identiti maker-checker. **LAPOR SAHAJA** (tiada pembaikan automatik);
  kod keluar 0 jika `Financial Integrity Exceptions = 0`.
- **Dokumentasi UAT/deployment** (`docs/`): `PANDUAN_ANALISIS_URS_HYBRID.md` (analisis & panduan URS Hybrid v1.1),
  `ROLE_PERMISSION_MATRIX.md` (dijana dari pemetaan sebenar),
  `UAT_CHECKLIST.md`, `UAT_ISSUE_TEMPLATE.md`, `SECURITY_CHECKLIST.md`, `DEPLOYMENT_READINESS.md` (+ backup/restore).
- **Disahkan (kekal utuh):** XSS (Blade escaping), mass-assignment (`$fillable`), IDOR (policy skop `alp_id`),
  keselamatan fail (storan peribadi, MIME/saiz/sambungan, SHA-256, nama rawak, immutable selepas dimuktamadkan),
  maker≠checker, idempotensi/konkurensi. Tiada kod nyahpepijat (`dd/dump/console.log/TODO`) ditemui.

## Budget Governance / Maker-Checker (Fasa 4A)

Peruntukan awal & pelarasan kini melalui aliran **maker-checker** — tiada tulisan kewangan langsung
ke ledger. Aliran: `Maker cipta cadangan → DRAFT → Submit → PENDING_APPROVAL → Checker → APPROVED →
transaksi ledger diposkan`.

- **MAKER** = Pegawai Kewangan (`allocations.request.*`, `adjustments.request.*`): cipta/sunting/hantar
  cadangan; **tidak boleh** meluluskan sendiri.
- **CHECKER** = Pelulus (`allocations.approve/reject/return`, `adjustments.approve/reject/return`):
  lulus/tolak/kembalikan. **MAKER ≠ CHECKER** dikuatkuasa oleh identiti (`approver != created_by && != submitted_by`)
  — terpakai juga kepada Super Admin (yang tindakannya diaudit sebagai `SUPER_ADMIN_FINANCIAL_OVERRIDE`).
- **System Admin TIDAK** menerima kebenaran kewangan (create/adjust/approve) secara automatik — hanya lihat.
- **ALP** hanya lihat peruntukan **DILULUSKAN**; cadangan menunggu tidak mengubah baki rasmi.
- **Jenis cadangan** (`budget_requests`): `INITIAL_ALLOCATION`, `ALLOCATION_INCREASE`, `ALLOCATION_DECREASE`
  (magnitud positif; arah dikawal enum, bukan nombor bertanda dari borang).
- **Kelulusan** (`BudgetRequestApprovalService`) — satu transaksi atomik: kunci cadangan + allocation,
  sahkan status/maker-checker/tahun/jumlah, semak belum diposkan, poskan **satu** transaksi ledger
  (dikaitkan `budget_request_id`, unique), tandakan APPROVED, sejarah + audit. Kegagalan → rollback penuh.
- **Idempotensi:** unique(`budget_request_id`) pada ledger + semakan status berkunci → klik berganda/serentak
  tidak boleh mempos dua kali.
- **Keselamatan pengurangan (DECREASE):** disemak semula pada kelulusan menggunakan kedudukan terkini —
  ditolak jika `Projected Available` (= Ledger Available selepas pengurangan − Pending Application) menjadi negatif,
  atau peruntukan baharu < committed + spent. Melindungi permohonan aktif daripada peruntukan dikurangkan.
- Duplikasi peruntukan awal dihalang (unique `allocations` alp×tahun + semakan cadangan aktif); tambahan dana = pelarasan.
- Ledger kekal **immutable** & **append-only**; pembetulan = pelarasan/pembalikan yang diluluskan.
- `budget_request_histories` merekod setiap peralihan status (append-only).
- **Laluan tulis kewangan langsung telah dibuang** — `BudgetService::allocate()/adjust()` kini hanya diposkan
  secara dalaman oleh servis kelulusan (tiada controller/HTTP memanggilnya terus; ujian membuktikan governance
  tidak boleh dipintas).

## Approval Logic (Fasa 4)

**Aliran:** `SUBMITTED → (Semakan Urus Setia) → UNDER_FINANCE_REVIEW → (Semakan Kewangan) →
UNDER_TECHNICAL_REVIEW (jika perlu) → PENDING_APPROVAL → APPROVED`. Cabang: `REVISION_REQUIRED`, `REJECTED`.
Peralihan dikawal oleh lapisan servis (`ApplicationReviewService`, `ApprovalService`) — tiada kemas kini
status sembarangan melalui controller.

- **Semakan** (`application_reviews`, append-only) berasingan daripada **kelulusan formal**
  (`application_approvals`, append-only). Keputusan semakan: RECOMMEND / RETURN_FOR_REVISION / NOT_RECOMMENDED
  (RECOMMEND & NOT_RECOMMENDED memajukan aliran; RETURN mengembalikan untuk pembetulan). **Semakan Kewangan
  bersifat nasihat — TIDAK mencipta komitmen.**
- **Semakan Teknikal berdasarkan konfigurasi** (`application_workflow_settings`): CSR = tidak perlu,
  DEVELOPMENT = perlu (boleh diubah).
- **Matriks kelulusan** (`approval_levels`, boleh konfig): jumlah menentukan band; kelulusan **berperingkat**
  diperlukan dari aras 1 hingga band tersebut. Julat bertindih ditolak; jumlah tanpa band gagal selamat
  ("Tiada matriks kelulusan dikonfigurasi untuk jumlah ini"). Aras dirujuk tidak dipadam — dinyahaktif.
- **Revisi/Resubmission:** pemulangan menyimpan snapshot bukti (`application_revisions`) dan menaikkan
  `revision_number`. Peraturan Fasa 4: penghantaran semula **memulakan semula semakan dari Urus Setia**;
  semakan/kelulusan pusingan sebelum dikekalkan sebagai sejarah. `REVISION_REQUIRED` **kekal dikira sebagai
  Pending Request** (kapasiti masih dirizab). `REJECTED`/`CANCELLED`/`APPROVED` tidak dikira pending.
- **Kelulusan akhir** (`ApprovalService::approve`) — satu transaksi atomik: kunci application + allocation,
  sahkan peringkat & aras, rekod kelulusan (unique per aras/pusingan), semak baki guna **Ledger Available
  sebenar** (bukan tolak pending sendiri), cipta **satu** `COMMITMENT` (unique `application_id`+`type`),
  tukar status ke APPROVED, sejarah + audit. Kegagalan → rollback penuh (tiada APPROVED tanpa komitmen,
  tiada komitmen tanpa APPROVED).
- **Idempotensi:** unique(`application_id`,`type`) pada ledger + unique(`application_id`,`approval_level_id`,
  `revision_number`) + kunci baris menghalang komitmen/kelulusan berganda (klik berganda / serentak).

## Modules

Lihat [Development Status](#development-status).

## Testing

```bash
php artisan test
```

- Fasa 1: 32 ujian (Authentication, Authorization/RBAC, Financial Year, User Management, ALP, Force Password Change).
- Fasa 2: Budget Ledger, Authorization, BudgetSummary + **ketepatan wang** (MoneyTest, BudgetPrecisionTest).
- Fasa 3: Application, Application Number, Budget Items, Documents, Submission, Concurrency.
- Fasa 4: Review Workflow, Approval Matrix, Final Approval, Idempotency/Rollback/Concurrency, Rejection & Revision.
- Fasa 4A: Budget Governance (maker-checker), no-ledger-before-approval, maker≠checker, adjustment/decrease safety, idempotency, atomicity, permissions.
- Fasa 5: Project creation, Lifecycle, Expense maker-checker, Commitment consumption, Closure & release, Idempotency/Concurrency/Atomicity.
- Fasa 5A: Evidence (perbelanjaan/penutupan/refund), Refund maker-checker & posting ledger, dokumen wajib penutupan, immutability.
- Fasa 6: Reporting financial exact (breakdown/refund/net/available/projected), financial-year isolation, pending≠committed, status counts, CSR null-beneficiary, reconciliation & data-quality exceptions, export XLSX/PDF (auth + content-type + filename), dashboard/report authorization & data isolation.
- Fasa 7: Export security (XLSX formula-injection guard, wang kekal numerik/tepat).
- **Ujian dijalankan terhadap MySQL 8.4** (`alp_dbkl_test`) supaya ketepatan DECIMAL/SUM/agregasi disahkan pada enjin sebenar.
- **Jumlah: 223 ujian, 474 assertion — semua lulus.** Integriti kewangan: `php artisan integrity:check`.

## Security

CSRF (lalai Laravel), hashing kata laluan, login throttling (5 percubaan), sekatan pengguna
tidak aktif, wajib tukar kata laluan, authorization backend (Gate/Policy/permission), dasar kata laluan
(min 8, huruf + nombor). Tiada public registration — akaun dicipta oleh pentadbir.
**Dokumen:** storan peribadi, muat turun berkuasa (Policy), whitelist jenis/saiz, tiada fail boleh-laksana,
laluan storan tidak didedah. **Kewangan:** operasi atomik + kunci baris untuk keselamatan serentak; audit trail.

## Development Status

| Fasa | Modul | Status |
|---|---|---|
| 0 | Discovery | ✅ Selesai |
| 1 | Auth, RBAC, User, ALP, Financial Year, layout/sidebar, dashboard shell | ✅ Selesai |
| 2 | Peruntukan tahunan, Pelarasan, Ledger immutable, Pengiraan baki, Overview, Audit kewangan | ✅ Selesai |
| 3 | Permohonan (wizard 6-langkah), Item bajet, Dokumen, Penghantaran, Pending Request, Audit | ✅ Selesai |
| 4 | Semakan (Urus Setia/Kewangan/Teknikal), Matriks kelulusan, Kelulusan berbilang-aras, Revisi, Penolakan, Komitmen Bajet | ✅ Selesai |
| 4A | Governance Bajet (Maker-Checker): cadangan peruntukan/pelarasan, kelulusan checker, keselamatan pengurangan, idempotensi | ✅ Selesai |
| 5 | Projek dari permohonan diluluskan, Kitaran hayat, Milestone/Kemajuan, Perbelanjaan (maker-checker), Penggunaan komitmen, Penutupan & pelepasan komitmen, Laporan akhir | ✅ Selesai |
| 5A | Bukti (perbelanjaan/penutupan/refund), Refund maker-checker + posting ledger, dokumen wajib penutupan | ✅ Selesai |
| 6 | Dashboard Eksekutif/Kewangan, Laporan (Kewangan/Permohonan/Projek/CSR/Audit/Maker-Checker), Rekonsiliasi & Kualiti Data, Eksport XLSX/PDF, penapis tahun, RBAC laporan | ✅ Selesai |
| **7** | **Hardening & UAT Readiness: had kadar, halaman ralat BM, guard formula-injection eksport, `integrity:check`, dokumentasi UAT/deployment/keselamatan/matriks peranan** | ✅ **Selesai (UAT READY)** |

## Roadmap

Ikut fasa di atas. Setiap fasa diakhiri dengan ujian + laporan sebelum fasa seterusnya bermula.

## Known Limitations

- MySQL dijalankan sebagai proses latar (bukan Windows service) pada mesin pembangunan semasa —
  perlu dimulakan semula selepas but semula.
- **Refund** (`REFUND`) — aliran UI/servis maker-checker penuh telah dibina pada **Fasa 5A** (refund
  memulihkan baki komitmen projek; dilepaskan ke available semasa penutupan). Refund terhadap projek **CLOSED**
  dihalang (tiada pembukaan semula senyap).
- **Pembatalan projek** dihalang jika ada perbelanjaan disahkan (> 0) — perlu penutupan terkawal (didokumen).
- **Eksport PDF/XLSX** dijana oleh penulis tulen dalaman (tanpa pakej luar) — ringkas & sah, bukan
  reka letak kompleks/pixel-perfect (selaras keperluan Fasa 6).
- **Prestasi pada skala besar:** penunjuk integriti Dashboard Eksekutif & ringkasan kewangan per-projek
  bertaraf `O(projek)`; laporan Ledger tidak berhalaman (baki berjalan perlukan jujukan penuh). Serta-merta
  pada isipadu UAT; untuk ratusan projek pertimbang cache/cron/paginasi — lihat `docs/DEPLOYMENT_READINESS.md` §7b.
- Bukti dokumen perbelanjaan/penutupan/refund: **muat naik UI penuh telah dibina pada Fasa 5A** (storan
  peribadi, SHA-256, sekatan jenis/saiz, immutable selepas dimuktamadkan).
- Peraturan resubmission Fasa 4: penghantaran semula memulakan semula semakan dari Urus Setia (bukan kembali
  ke peringkat yang meminta pembetulan) — dipilih untuk kesederhanaan & keselamatan; boleh diperhalusi kemudian.
- Notifikasi (in-app/e-mel) belum dibina — keutamaan pada integriti kewangan/aliran kerja.
- Data ledger sedia ada (sebelum Fasa 4A) kekal sah sebagai garis dasar sejarah; DevSeeder kini mencipta
  peruntukan melalui aliran maker-checker (cadangan diluluskan + rekod ledger sepadan) supaya sejarah konsisten.
- `BudgetService::allocate()/adjust()` kekal sebagai API PHP dalaman (untuk servis kelulusan & persediaan ujian)
  tetapi TIDAK dipanggil oleh mana-mana controller/laluan HTTP — governance tidak boleh dipintas.
- Item bajet menggunakan **kuantiti integer** pada Fasa 3 (harga & jumlah kekal `DECIMAL(15,2)` tepat).
  Kuantiti perpuluhan boleh ditambah kemudian jika diperlukan.
- Nombor permohonan dijana semasa draf dicipta; draf terbatal boleh menyebabkan jurang nombor (diterima).
- Item bajet Fasa 3 menyokong Tambah/Buang; suntingan baris dibuat dengan buang & tambah semula.
- Keselamatan serentak penghantaran dijamin oleh `lockForUpdate`; ujian membuktikan penolakan lebih-langgan
  (paling banyak satu daripada dua penghantaran berjaya).
- Ujian memerlukan pangkalan data MySQL `alp_dbkl_test` (dicipta secara setempat); RefreshDatabase memigrasi skema tersebut.
- E-mel (reset kata laluan) menggunakan pemacu `log`/`array` dalam pembangunan.

---

_Data pembangunan (DevSeeder) — log masuk contoh: `superadmin@dbkl.test` / `password`. Jangan guna dalam production._
