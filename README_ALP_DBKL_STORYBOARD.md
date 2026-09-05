# Sistem Ahli Lembaga Penasihat (ALP) DBKL
## Storyboard & Cadangan Struktur Sistem

**Versi:** Draft 0.1  
**Organisasi:** Dewan Bandaraya Kuala Lumpur (DBKL)  
**Jenis Sistem:** Sistem Korporat Pengurusan Peruntukan, Permohonan Projek & CSR

---

# 1. Ringkasan Sistem

Sistem ALP DBKL ialah sistem korporat untuk mengurus:

- Peruntukan bajet tahunan kepada setiap Ahli Lembaga Penasihat (ALP)
- Permohonan projek pembangunan / Corporate Social Responsibility (CSR)
- Muat naik kertas kerja dan dokumen sokongan
- Semakan oleh urus setia / pegawai DBKL
- Semakan kewangan
- Semakan teknikal jika diperlukan
- Kelulusan / penolakan / pembetulan
- Penguncian komitmen bajet selepas kelulusan
- Pemantauan pelaksanaan projek
- Rekod perbelanjaan sebenar
- Laporan impak dan penutupan projek
- Audit trail penuh

Matlamat utama sistem ialah memastikan keseluruhan proses peruntukan dan kelulusan projek ALP dibuat secara **telus, tersusun, boleh dijejak dan berasaskan baki bajet sebenar**.

---

# 2. Konsep Utama

Setiap ALP mempunyai peruntukan tahunan.

Contoh:

| ALP | Bajet Tahunan | Committed | Actual Spent | Baki |
|---|---:|---:|---:|---:|
| ALP 01 | RM500,000 | RM120,000 | RM80,000 | RM300,000 |
| ALP 02 | RM500,000 | RM250,000 | RM100,000 | RM150,000 |
| ALP 03 | RM500,000 | RM50,000 | RM20,000 | RM430,000 |

Bajet dibezakan kepada empat status utama:

1. **Available** — masih tersedia
2. **Pending / Requested** — sedang dipohon
3. **Committed** — telah diluluskan dan dikunci
4. **Actual Spent** — perbelanjaan sebenar

---

# 3. Peranan Pengguna

## 3.1 Ahli Lembaga Penasihat (ALP)

Boleh:

- Melihat bajet sendiri
- Melihat baki peruntukan
- Membuat permohonan projek
- Menyemak status permohonan
- Melihat projek aktif
- Melihat sejarah projek
- Melihat jumlah perbelanjaan sendiri

---

## 3.2 Urus Setia ALP

Boleh:

- Membantu menyediakan permohonan
- Mengisi maklumat projek
- Muat naik dokumen
- Mengemaskini dokumen yang dikembalikan
- Memantau status permohonan

---

## 3.3 Pegawai Semakan DBKL

Boleh:

- Semak kelengkapan permohonan
- Semak kertas kerja
- Beri komen
- Kembalikan untuk pembetulan
- Hantar ke peringkat seterusnya

---

## 3.4 Pegawai Kewangan

Boleh:

- Semak baki bajet
- Sahkan jumlah permohonan
- Semak pecahan kos
- Sahkan komitmen bajet
- Rekod pembayaran / perbelanjaan sebenar

---

## 3.5 Pegawai Teknikal

Jika projek berbentuk pembangunan:

- Semak skop
- Semak lokasi
- Semak spesifikasi
- Beri ulasan teknikal
- Syor lulus / pindaan / tolak

---

## 3.6 Pelulus / Jawatankuasa

Boleh:

- Melihat keseluruhan permohonan
- Melihat ulasan pegawai
- Melihat kedudukan bajet
- Meluluskan
- Menolak
- Meminta pembetulan

---

## 3.7 Pengurusan DBKL

Boleh melihat:

- Dashboard keseluruhan
- Jumlah bajet ALP
- Jumlah komitmen
- Jumlah perbelanjaan
- Projek aktif
- Projek selesai
- Prestasi mengikut ALP
- Prestasi mengikut kategori
- CSR impact

---

## 3.8 Pentadbir Sistem

Boleh mengurus:

- Pengguna
- Role
- Tahun kewangan
- Bajet tahunan
- Kategori projek
- Approval matrix
- Had kuasa
- Status
- Tetapan sistem

---

# 4. Workflow Sistem

```text
DRAF
  ↓
HANTAR PERMOHONAN
  ↓
SEMAKAN URUS SETIA
  ↓
SEMAKAN KEWANGAN
  ↓
SEMAKAN TEKNIKAL (JIKA PERLU)
  ↓
KELULUSAN / JAWATANKUASA
  ↓
BAJET DIKUNCI / COMMITTED
  ↓
PELAKSANAAN PROJEK
  ↓
KEMASKINI PERBELANJAAN
  ↓
LAPORAN AKHIR
  ↓
SELESAI / DITUTUP
```

Status tambahan:

```text
PERLU PEMBETULAN
DITOLAK
DIBATALKAN
```

---

# 5. Modul Utama

1. Dashboard
2. Ahli Lembaga
3. Peruntukan Bajet
4. Permohonan Projek
5. Semakan
6. Kelulusan
7. Projek
8. Kewangan
9. Dokumen
10. Laporan
11. Audit Trail
12. Notifikasi
13. Pentadbiran Sistem

---

# 6. Storyboard UI

---

# Screen 1 — Login

```text
┌──────────────────────────────────────────────────────┐
│                        DBKL                          │
│                                                      │
│          SISTEM AHLI LEMBAGA PENASIHAT              │
│                                                      │
│         Pengurusan Peruntukan & Projek CSR           │
│                                                      │
│   ID Pengguna   [________________________]           │
│   Kata Laluan   [________________________]           │
│                                                      │
│              [      LOG MASUK      ]                 │
│                                                      │
└──────────────────────────────────────────────────────┘
```

Konsep:

- Bersih
- Formal
- Korporat
- Logo DBKL
- Warna utama biru gelap / putih / emas lembut

---

# Screen 2 — Dashboard ALP

```text
┌─────────────────────────────────────────────────────────────────────┐
│ DBKL | SISTEM ALP                                  ALP: AHMAD ▼    │
├──────────────┬──────────────────────────────────────────────────────┤
│ Dashboard    │                                                      │
│ Permohonan   │   BAJET 2026                                        │
│ Projek Saya  │                                                      │
│ Dokumen      │   RM500,000     RM120,000     RM80,000    RM300,000 │
│ Laporan      │   Peruntukan     Committed     Spent       Baki      │
│ Profil       │                                                      │
│              ├──────────────────────────────────────────────────────┤
│              │ Penggunaan Bajet                                    │
│              │ ████████████████████░░░░░░░░░░ 40%                  │
│              ├──────────────────────────────────────────────────────┤
│              │ Permohonan Terkini                                  │
│              │                                                      │
│              │ CSR-026  Program Komuniti      RM85,000   SEMAKAN   │
│              │ DEV-011  Naik Taraf Dewan      RM60,000   LULUS     │
│              │ CSR-021  Program Pendidikan    RM40,000   SELESAI   │
└──────────────┴──────────────────────────────────────────────────────┘
```

---

# Screen 3 — Dashboard Pengurusan DBKL

```text
┌────────────────────────────────────────────────────────────────────┐
│ DASHBOARD PENGURUSAN DBKL                                         │
├────────────────┬────────────────┬────────────────┬─────────────────┤
│ TOTAL BAJET    │ COMMITTED      │ ACTUAL SPENT   │ BAKI            │
│ RM10.0 Juta    │ RM4.1 Juta     │ RM2.8 Juta     │ RM3.1 Juta      │
├────────────────┴────────────────┴────────────────┴─────────────────┤
│                                                                    │
│ Penggunaan Bajet Mengikut ALP                                     │
│                                                                    │
│ ALP 01 █████████████████ 72%                                      │
│ ALP 02 ████████████      55%                                      │
│ ALP 03 ███████████████   68%                                      │
│ ALP 04 ████████          37%                                      │
│                                                                    │
├──────────────────────────────────────┬─────────────────────────────┤
│ Permohonan Mengikut Status           │ Tindakan Diperlukan         │
│                                      │                             │
│ Menunggu Semakan        12           │ 8 Semakan                  │
│ Menunggu Kewangan        7           │ 4 Kelulusan                │
│ Menunggu Kelulusan       4           │ 3 Pembetulan               │
│ Diluluskan              38           │                             │
└──────────────────────────────────────┴─────────────────────────────┘
```

---

# Screen 4 — Senarai ALP

```text
┌──────────────────────────────────────────────────────────────────────────┐
│ AHLI LEMBAGA PENASIHAT                                                  │
├───────────────┬──────────────┬─────────────┬─────────────┬───────────────┤
│ Nama          │ Bajet        │ Committed   │ Spent       │ Baki          │
├───────────────┼──────────────┼─────────────┼─────────────┼───────────────┤
│ ALP 01        │ RM500,000    │ RM120,000   │ RM80,000    │ RM300,000     │
│ ALP 02        │ RM500,000    │ RM250,000   │ RM100,000   │ RM150,000     │
│ ALP 03        │ RM500,000    │ RM50,000    │ RM20,000    │ RM430,000     │
└───────────────┴──────────────┴─────────────┴─────────────┴───────────────┘
```

Klik nama ALP → profil terperinci.

---

# Screen 5 — Profil ALP

```text
┌───────────────────────────────────────────────────────────────┐
│ PROFIL ALP                                                    │
├───────────────────────────────────────────────────────────────┤
│ Nama              : YBhg. / YBrs. XXXXX                      │
│ Portfolio/Kawasan : XXXXX                                    │
│ Tahun             : 2026                                     │
│                                                               │
│ Bajet             : RM500,000                                │
│ Committed         : RM120,000                                │
│ Actual Spent      : RM80,000                                 │
│ Available         : RM300,000                                │
├───────────────────────────────────────────────────────────────┤
│ PROJEK                                                        │
│                                                               │
│ CSR-026 | Program Komuniti       | RM85,000 | Semakan        │
│ DEV-011 | Naik Taraf Dewan       | RM60,000 | Diluluskan     │
└───────────────────────────────────────────────────────────────┘
```

---

# Screen 6 — Permohonan Baharu

Sistem menggunakan wizard.

```text
1 Maklumat Projek
        ↓
2 Skop & Objektif
        ↓
3 Bajet
        ↓
4 Dokumen
        ↓
5 Semakan
        ↓
6 Hantar
```

## Step 1 — Maklumat Projek

```text
Kategori Projek
[ CSR ▼ ]

Nama Projek
[ Program Komuniti PPR Seri Murni              ]

Lokasi
[ PPR Seri Murni                               ]

Tarikh Mula
[ 20/09/2026 ]

Tarikh Tamat
[ 21/09/2026 ]

Objektif
[ ____________________________________________ ]
[ ____________________________________________ ]

                         [Simpan Draf] [Seterusnya]
```

---

# Screen 7 — Bajet Permohonan

```text
┌──────────────────────────────────────────────────────────────┐
│ PECAHAN BAJET                                                │
├─────────────────────────────┬─────────┬─────────┬────────────┤
│ Item                        │ Qty     │ Unit    │ Jumlah     │
├─────────────────────────────┼─────────┼─────────┼────────────┤
│ Khemah                      │ 10      │ RM500   │ RM5,000    │
│ Makanan                     │ 500     │ RM20    │ RM10,000   │
│ Logistik                    │ 1       │ RM5,000 │ RM5,000    │
├─────────────────────────────┴─────────┴─────────┼────────────┤
│                                      TOTAL      │ RM20,000   │
└─────────────────────────────────────────────────┴────────────┘

Bajet tersedia ALP     : RM300,000
Jumlah permohonan      : RM20,000
Baki selepas kelulusan : RM280,000
```

---

# Screen 8 — Dokumen

```text
Kertas Kerja
[ Upload PDF ]

Sebut Harga
[ Upload ]

Surat Sokongan
[ Upload ]

Pelan / Gambar Lokasi
[ Upload ]

Dokumen Tambahan
[ Upload ]

Checklist:

✓ Kertas kerja
✓ Pecahan bajet
✓ Sebut harga
✓ Lokasi
○ Surat sokongan
```

Sistem boleh menetapkan dokumen wajib mengikut kategori projek.

---

# Screen 9 — Detail Permohonan

```text
CSR-2026-0041
PROGRAM KOMUNITI PPR SERI MURNI

RM85,000                         MENUNGGU SEMAKAN

[Ringkasan] [Bajet] [Dokumen] [Semakan] [Kelulusan] [Aktiviti]
```

Ringkasan:

```text
ALP               : ALP 01
Kategori          : CSR
Lokasi            : PPR Seri Murni
Tarikh            : 20-21 September 2026
Jumlah Dipohon    : RM85,000
Baki Bajet        : RM300,000
Baki Jika Lulus   : RM215,000
```

---

# Screen 10 — Timeline / Tracking

```text
✓ 01/08/2026  Draf dibuat
✓ 03/08/2026  Permohonan dihantar
✓ 04/08/2026  Semakan Urus Setia
● 05/08/2026  Semakan Kewangan
○              Semakan Teknikal
○              Kelulusan
○              Pelaksanaan
```

Pengguna boleh nampak kedudukan permohonan tanpa perlu telefon pegawai.

---

# Screen 11 — Semakan Pegawai

```text
PERMOHONAN CSR-2026-0041

Jumlah Dipohon       RM85,000
Baki ALP             RM300,000
Baki Jika Diluluskan RM215,000

Kelengkapan Dokumen
✓ Kertas Kerja
✓ Bajet
✓ Sebut Harga
✓ Lokasi

Ulasan Pegawai
[ ______________________________________________ ]
[ ______________________________________________ ]

[ Kembalikan Untuk Pembetulan ]
[ Syor Tolak ]
[ Hantar Ke Kewangan ]
```

---

# Screen 12 — Semakan Kewangan

```text
SEMAKAN KEWANGAN

Bajet Tahunan        RM500,000
Committed            RM120,000
Actual Spent         RM80,000
Available            RM300,000

Permohonan           RM85,000

Baki Selepas Lulus   RM215,000

Status Bajet:
✓ PERUNTUKAN MENCUKUPI

Catatan
[ ____________________________________________ ]

[ Kembali ]
[ Sahkan Bajet ]
```

---

# Screen 13 — Kelulusan

```text
KELULUSAN PERMOHONAN

CSR-2026-0041
Program Komuniti PPR Seri Murni

Jumlah               RM85,000
Kategori             CSR
Baki selepas lulus   RM215,000

Semakan Urus Setia   ✓
Semakan Kewangan     ✓
Semakan Teknikal     N/A

Ulasan:
"Dokumen lengkap dan bajet mencukupi."

[ TOLAK ]
[ KEMBALIKAN ]
[ LULUSKAN ]
```

Selepas diluluskan:

```text
Status Bajet:
AVAILABLE → COMMITTED
```

---

# Screen 14 — Projek Aktif

```text
PROGRAM KOMUNITI PPR SERI MURNI

Status       : DALAM PELAKSANAAN
Progress     : 60%
Peruntukan   : RM85,000
Spent        : RM42,500
Baki Projek  : RM42,500

██████████████████░░░░░░░░░ 60%

Milestone

✓ Penyediaan vendor
✓ Logistik
● Pelaksanaan program
○ Laporan akhir
```

---

# Screen 15 — Perbelanjaan

```text
TRANSAKSI

01/09/2026 Vendor A        RM15,000
05/09/2026 Vendor B        RM12,500
10/09/2026 Logistik        RM15,000

Jumlah Diluluskan          RM85,000
Actual Spent               RM42,500
Baki                       RM42,500
```

---

# Screen 16 — Laporan Akhir Projek

Maklumat:

- Tarikh siap
- Jumlah penerima manfaat
- Perbelanjaan sebenar
- Baki
- Outcome
- Gambar sebelum
- Gambar selepas
- Laporan akhir

Contoh:

```text
Jumlah Diluluskan : RM85,000
Actual Spent      : RM81,700
Penjimatan        : RM3,300

Penerima Manfaat  : 750 orang

Status
✓ SELESAI
```

---

# Screen 17 — Dashboard CSR Impact

```text
CSR IMPACT 2026

Jumlah Projek       128
Jumlah Peruntukan   RM6.2 Juta
Penerima Manfaat    85,200 orang
Projek Selesai      93

Kategori

Komuniti       38%
Pendidikan     24%
Kesihatan      18%
Belia/Sukan    12%
Lain-lain       8%
```

---

# Screen 18 — Laporan

Laporan yang dicadangkan:

- Laporan Peruntukan Mengikut ALP
- Laporan Baki Bajet
- Laporan Projek Mengikut Status
- Laporan CSR
- Laporan Projek Pembangunan
- Laporan Perbelanjaan
- Laporan Prestasi ALP
- Laporan Projek Lewat
- Laporan Pembatalan / Penolakan
- Audit Trail
- Laporan Tahunan

Format:

- Paparan dashboard
- PDF
- Excel

---

# 7. Approval Matrix

Contoh:

| Jumlah | Kelulusan |
|---:|---|
| ≤ RM20,000 | Pegawai / Ketua Unit |
| RM20,001 – RM100,000 | Pengarah |
| > RM100,000 | Jawatankuasa / Kelulusan Tertinggi |

Had sebenar perlu ditetapkan mengikut polisi DBKL.

---

# 8. Status Permohonan

```text
DRAFT
SUBMITTED
UNDER_SECRETARIAT_REVIEW
UNDER_FINANCE_REVIEW
UNDER_TECHNICAL_REVIEW
PENDING_APPROVAL
REVISION_REQUIRED
APPROVED
REJECTED
CANCELLED
IN_PROGRESS
COMPLETED
CLOSED
```

---

# 9. Notifikasi

Sistem menghantar notifikasi apabila:

- Permohonan dihantar
- Permohonan diterima
- Permohonan perlu pembetulan
- Semakan selesai
- Kelulusan diberi
- Permohonan ditolak
- Projek hampir tarikh tamat
- Laporan akhir belum dihantar
- Bajet hampir habis

Saluran:

- Notifikasi dalam sistem
- E-mel

SMS / WhatsApp boleh dipertimbangkan kemudian.

---

# 10. Audit Trail

Setiap perubahan penting direkodkan:

```text
Tarikh / Masa
Pengguna
Role
Tindakan
Status Sebelum
Status Selepas
Catatan
IP / Session Reference
```

Contoh:

```text
05/08/2026 10:32
Ahmad - Pegawai Kewangan
"Sahkan Bajet"
UNDER_FINANCE_REVIEW → PENDING_APPROVAL
```

---

# 11. Struktur Menu

```text
Dashboard

Ahli Lembaga
├── Senarai ALP
├── Profil ALP
└── Prestasi ALP

Peruntukan
├── Bajet Tahunan
├── Pelarasan
└── Baki

Permohonan
├── Permohonan Baharu
├── Semua Permohonan
├── Menunggu Semakan
├── Perlu Pembetulan
└── Diluluskan

Kelulusan
├── Semakan
├── Kewangan
├── Teknikal
└── Kelulusan

Projek
├── Projek Aktif
├── Projek Selesai
└── Projek Lewat

Laporan
├── Bajet
├── Projek
├── CSR
├── Prestasi
└── Audit

Pentadbiran
├── Pengguna
├── Role
├── Approval Matrix
├── Kategori
└── Tetapan
```

---

# 12. Cadangan Rekod Utama

Entiti asas:

```text
User
Role
ALP
FinancialYear
Allocation
BudgetTransaction
Application
ApplicationBudget
ApplicationDocument
ApplicationReview
Approval
Project
ProjectExpense
ProjectMilestone
ProjectReport
Notification
AuditLog
```

---

# 13. Prinsip Sistem

Sistem mesti memenuhi prinsip berikut:

### 1. Tiada bajet ghaib
Semua peruntukan, komitmen dan perbelanjaan boleh dijejak.

### 2. Tiada kelulusan tanpa dokumen
Dokumen wajib mesti lengkap sebelum proses diteruskan.

### 3. Tiada over-budget
Sistem memberi amaran atau menyekat permohonan melebihi baki.

### 4. Semua tindakan direkodkan
Setiap semakan, komen dan kelulusan mempunyai audit trail.

### 5. Satu sumber kebenaran
Status permohonan dan status bajet berada dalam sistem yang sama.

### 6. Mudah untuk pengurusan
Pengurusan boleh memahami kedudukan keseluruhan dalam satu dashboard.

---

# 14. Cadangan Gaya UI

Tema dicadangkan:

- Korporat DBKL
- Clean
- Minimal
- Banyak ruang putih
- Navy / Royal Blue
- Warna status yang jelas
- Card based dashboard
- Responsive desktop / tablet / mobile
- Fokus kepada nombor bajet dan status tindakan

Contoh warna status:

```text
DRAFT            Kelabu
PENDING          Amber
UNDER REVIEW     Biru
REVISION         Jingga
APPROVED         Hijau
REJECTED         Merah
COMPLETED        Hijau Gelap
```

---

# 15. Cadangan Dashboard Top Management

Paparan ringkas:

```text
Total Budget
Committed
Actual Spend
Available

Applications
Approved
Pending
Rejected

Projects
Active
Delayed
Completed

CSR Impact
Beneficiaries
Locations
Total CSR Spend
```

Dengan:

- Trend bulanan
- Perbandingan ALP
- Map lokasi projek
- Kategori projek
- Top 5 projek
- Projek lewat
- Baki bajet rendah

---

# 16. Cadangan Fasa Pembangunan

## Phase 1 — Core System

- Login
- Role
- ALP
- Bajet
- Permohonan
- Dokumen
- Semakan
- Kelulusan
- Dashboard asas

## Phase 2 — Project Monitoring

- Projek aktif
- Milestone
- Expense
- Laporan akhir
- CSR impact

## Phase 3 — Management Intelligence

- Advanced dashboard
- Analytics
- Map
- Prestasi ALP
- KPI
- Automated reporting

## Phase 4 — Integration

Jika diperlukan:

- Sistem kewangan DBKL
- SSO
- E-mel DBKL
- Digital signature
- e-Procurement
- GIS
- Document Management System

---

# 17. Gambaran Keseluruhan

```text
                         DBKL
                          │
                          ▼
                 PERUNTUKAN BAJET
                          │
                          ▼
                         ALP
                          │
                          ▼
               PERMOHONAN PROJEK / CSR
                          │
                          ▼
              KERTAS KERJA + DOKUMEN
                          │
                          ▼
                    SEMAKAN DBKL
                          │
                  ┌───────┴────────┐
                  ▼                ▼
             PEMBETULAN          TERUSKAN
                                   │
                                   ▼
                         SEMAKAN KEWANGAN
                                   │
                                   ▼
                        SEMAKAN TEKNIKAL
                            (JIKA PERLU)
                                   │
                                   ▼
                              KELULUSAN
                                   │
                                   ▼
                         BAJET COMMITTED
                                   │
                                   ▼
                         PELAKSANAAN PROJEK
                                   │
                                   ▼
                          ACTUAL EXPENDITURE
                                   │
                                   ▼
                           LAPORAN & IMPAK
                                   │
                                   ▼
                              PROJECT CLOSED
```

---

# 18. Hasil Yang Dijangka

Dengan sistem ini, DBKL boleh melihat secara masa nyata:

- Siapa menerima berapa bajet
- Siapa sudah menggunakan bajet
- Berapa baki setiap ALP
- Projek apa yang sedang dipohon
- Permohonan berada di meja siapa
- Jumlah yang telah diluluskan
- Jumlah sebenar dibelanjakan
- Projek yang lambat
- Projek CSR yang memberi impak
- Sejarah penuh setiap keputusan

---

# 19. Status Dokumen

**Status:** STORYBOARD DRAFT UNTUK SEMAKAN

Cadangan seterusnya selepas storyboard dipersetujui:

1. Finalkan workflow
2. Finalkan role
3. Finalkan approval matrix
4. Finalkan struktur bajet
5. Sediakan prototype UI lengkap
6. Sediakan database schema
7. Sediakan technical architecture
8. Mula pembangunan sistem
