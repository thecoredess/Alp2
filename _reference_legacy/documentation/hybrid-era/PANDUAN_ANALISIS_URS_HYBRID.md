# Panduan Analisis — URS Hybrid Sistem ALP DBKL

**Versi panduan:** 1.0  
**Tarikh:** 27 Ogos 2026  
**Nota (5 Sep 2026):** Analisis ini merujuk URS Hybrid v1.0/v1.1. **URS terkini ialah v1.2** — lihat [PANDUAN_ANALISIS_URS_v1.2.md](PANDUAN_ANALISIS_URS_v1.2.md).  
**Skop:** Analisis menyeluruh URS v1.0 → v1.1 Hybrid, pemetaan sistem Laravel, pelaksanaan Fasa A–D, dan panduan operasi/UAT  
**Audiens:** Pemilik proses JPM, Urus Setia, pasukan UAT, pasukan teknikal, pentadbir sistem

---

## Indeks dokumen berkaitan

| Dokumen | Lokasi | Kegunaan |
|---------|--------|----------|
| **Panduan ini** | `docs/PANDUAN_ANALISIS_URS_HYBRID.md` | Analisis & panduan utama (baca dari sini) |
| Addendum Hybrid | `docs/URS_ADDENDUM_HYBRID_v1.1.md` | Keputusan rasmi cadangan untuk URS |
| Kemas kini Word | `docs/URS_PANDUAN_KEMASKINI_v1.1.md` | Semakan manual URS v1.1 dalam Word |
| URS Word v1.0 | `URS/..._Standard_v1.0.docx` | Draf asal (25 Ogos 2026) |
| URS Word v1.1 | `URS/..._Standard_v1.1.docx` | Dijana skrip — Bah. 6 + §18 Addendum |
| UAT Checklist | `docs/UAT_CHECKLIST.md` | Senario UAT ikut peranan |
| Matriks peranan | `docs/ROLE_PERMISSION_MATRIX.md` | RBAC sebenar (Fasa 7) |
| Laporan Fasa 7 | `docs/PHASE_7_REPORT.md` | Kesediaan UAT asas |
| README sistem | `README.md` | Arkitektur, ledger, workflow korporat |
| Skrip jana URS | `scripts/build_urs_v1_1.php` | Jana semula fail Word v1.1 |
| Skrip sahkan URS | `scripts/verify_urs_v1_1.php` | Semak kandungan v1.1 |

---

## 1. Ringkasan eksekutif

### Apa yang dianalisis

Dokumen **URS Sistem Pengurusan Permohonan & Peruntukan Sumbangan ALP DBKL** (Draf v1.0) menggariskan sistem sumbangan kecil ALP (had RM30,000/tahun, RM3,000/permohonan, tempoh 4-bulan, baucar). Sistem Laravel sedia ada pula dibina sebagai **platform korporat** bajet/projek (ledger, maker-checker, projek CSR/pembangunan, belanja, refund).

Kedua-dua model **tidak selaras** sepenuhnya. Selepas analisis, keputusan **Hybrid** dilaksanakan:

| Aspek | Keputusan |
|-------|-----------|
| Platform korporat | **Kekal** — ledger, projek, belanja, refund, matriks kelulusan |
| Polisi sumbangan URS (BR-001…004) | **Lapisan pilihan** — lalai **OFF** |
| Pembayaran URS (baucar) | **Metadata** pada permohonan — bukan integrasi bank |
| Belanja projek | **Aliran berasingan** — baucar ≠ EXPENDITURE ledger |
| URS rasmi | **v1.1** dengan Addendum Hybrid + Bah. 6 diisi |

### Status pelaksanaan

| Fasa | Skop | Status |
|------|------|--------|
| **A** | Polisi URS, notifikasi, surat kelulusan, UI dashboard | ✅ Selesai |
| **B** | Tempoh 4-bulan, tertunggak, label Peraku/Pelulus | ✅ Selesai |
| **C** | Modul baucar/pembayaran + CSV | ✅ Selesai |
| **D** | Addendum MD, URS Word v1.1, canvas analisis | ✅ Selesai |
| **UAT rasmi** | Pengesahan pemilik proses P01–P17 | ⏳ Menunggu |
| **Production** | HTTPS, SMTP, backup, APP_DEBUG=false | ⏳ Belum disahkan |

### Hasil analisis fit (selepas Hybrid)

| Kategori | Bilangan modul URS | Nota |
|----------|-------------------|------|
| Selaras | 9 / 11 | M2, M3, M5, M6, M8, M9, M10, Notifikasi, dll. |
| Separa | 2 / 11 | M1 (tiada self-register), M7 (laporan program) |
| Gap | 0 / 11 | Jurang utama ditutup Fasa A–C |
| Lebihan sistem | 1 / 11 | M4 — semakan Kewangan + Teknikal (skop rasmi hybrid) |

**Ujian automatik Hybrid:** 18 ujian (`UrsPolicyAndNotificationTest`, `UrsPeriodAndOverdueTest`, `ApplicationPaymentTest`) — lulus.

---

## 2. Latar belakang — jurang asal URS vs sistem

### Perbezaan model utama (sebelum Hybrid)

| Dimensi | URS v1.0 (fokus) | Sistem Laravel (as-built) |
|---------|------------------|---------------------------|
| Skala wang | Sumbangan kecil (max RM3k/permohonan) | Projek CSR/pembangunan berskala lebih besar |
| Bajet | Had tetap RM30k/tahun, 3 tempoh | Ledger fleksibel + maker-checker |
| Semakan | 1 peringkat JP | 3 peringkat: Urus Setia, Kewangan, Teknikal |
| Kelulusan | Peraku + Pelulus (2 aras) | Matriks kelulusan berbilang aras |
| Pembayaran | Status baucar kepada ALP | Perbelanjaan projek → EXPENDITURE |
| Notifikasi | Wajib (e-mel + in-app) | Tiada (sebelum Fasa A) |
| Bah. 6 Luar skop | Kosong | Perlu ditetapkan |
| P01–P17 | Menunggu pengesahan | Perlu keputusan rasmi |

### Mengapa Hybrid, bukan salah satu sahaja?

**Pilihan ditolak:** Buang modul projek/ledger → sistem jadi terlalu sempit untuk program CSR/pembangunan DBKL yang sedia wujud.

**Pilihan dipilih:** Kekalkan platform korporat + tambah polisi URS sebagai **suis operasi**:
- **Polisi OFF** (lalai): UAT dan projek besar tanpa had RM3k/30k/tempoh.
- **Polisi ON**: Operasi sumbangan ketat selaras BR URS.

---

## 3. Model dwi-aliran kewangan (wajib difahami)

Ini prinsip teras Hybrid. **Dua aliran tidak bercampur** dalam ledger.

### Aliran A — Sumbangan ALP / baucar (URS)

```
ALP cipta permohonan (wizard)
    → Hantar (SUBMITTED)
    → Semakan Urus Setia → Kewangan → Teknikal
    → Kelulusan Peraku/Pelulus (matriks)
    → COMMITMENT ledger + projek dijana
    → Status baucar: Menunggu → Baucar Disedia → Dibayar
    → [Jika Polisi ON] BR-001…004 dikuatkuasa
```

**Ciri penting:**
- Status baucar disimpan pada jadual `applications` (`payment_*` fields).
- **Tidak** mencipta transaksi `EXPENDITURE` apabila baucar ditandakan Dibayar.
- No. baucar direkod manual (tiada integrasi ERP DBKL — OS-01).

### Aliran B — Belanja projek (korporat)

```
Pegawai Kewangan rekod perbelanjaan (maker)
    → Hantar untuk pengesahan
    → Pelulus sahkan (checker, maker ≠ checker)
    → EXPENDITURE ledger (mengurangkan baki komitmen projek)
    → Refund / penutupan mengikut peraturan sedia ada
```

**Ciri penting:**
- Perbelanjaan projek = bukti + maker-checker + ledger.
- Baucar (Aliran A) dan belanja (Aliran B) **berbeza tujuan dan rekod**.

### Diagram ringkas

```
                    ┌─────────────────────┐
                    │  Permohonan diluluskan │
                    └──────────┬──────────┘
                               │
              ┌────────────────┴────────────────┐
              ▼                                 ▼
     ┌─────────────────┐              ┌─────────────────┐
     │  ALiran A       │              │  Aliran B       │
     │  Status baucar  │              │  Belanja projek │
     │  (metadata)     │              │  (ledger)       │
     └─────────────────┘              └─────────────────┘
              │                                 │
              ▼                                 ▼
     payment_status field              budget_transactions
     /pembayaran + CSV                 type = EXPENDITURE
```

---

## 4. Analisis modul URS → sistem

| Modul | URS | Sistem | Fit | Catatan |
|-------|-----|--------|-----|---------|
| **M1** | Login & pengguna | Auth + RBAC Spatie | Separa | Tiada self-register ALP (OS-03); akaun oleh pentadbir |
| **M2** | Dashboard | Dashboard + notifikasi + tertunggak + kuota | Selaras | Kuota tempoh hanya bila Polisi ON |
| **M3** | Permohonan sumbangan | Wizard 6 langkah + Polisi URS | Selaras | BR-001…004 pada hantar/semula hantar bila ON |
| **M4** | Semakan JP | 3 semakan (US/Kewangan/Teknikal) | Lebihan | Skop rasmi hybrid — perlu dinyatakan dalam URS v1.1 |
| **M5** | Peraku + Pelulus | Matriks + surat kelulusan | Selaras | Label UI: Menunggu Peraku/Pelulus |
| **M6** | Pengurusan pembayaran | `/pembayaran` + belanja projek | Selaras | Baucar metadata; CSV eksport; tiada integrasi bank |
| **M7** | Laporan program | Laporan akhir projek + penutupan | Separa | Aliran pengesahan penerimaan kurang eksplisit |
| **M8** | Laporan & analisis | Modul laporan Fasa 6 | Selaras | XLSX/PDF; CSV untuk pembayaran |
| **M9** | Jejak audit | `audit_logs` + laporan audit | Selaras | Semak IP jika diperlukan UAT |
| **M10** | Tetapan sistem | FY, matriks, dokumen, Polisi URS | Selaras | `/tetapan/polisi-urs` |
| **—** | Notifikasi (Bah. 11) | DB + e-mel + loceng header | Selaras | 6+ jenis acara |

---

## 5. Analisis peraturan perniagaan (BR)

| ID | Peraturan URS | Status Hybrid | Pelaksanaan |
|----|---------------|---------------|-------------|
| BR-001 | Maks RM30,000/ALP/tahun | Selaras (bila ON) | `UrsContributionPolicy::maxAnnualAllocation()` |
| BR-002 | Maks RM3,000/permohonan | Selaras (bila ON) | Validasi `ApplicationSubmissionService` |
| BR-003 | 3 tempoh × RM10,000 | Selaras (bila ON) | Jan–Apr, Mei–Ogos, Sep–Dis |
| BR-004 | Baki tempoh luput | Selaras (bila ON) | Tiada carry-forward |
| BR-005 | Tiada had bilangan permohonan | Selaras | Kekal |
| BR-006 | Peruntukan individu | Selaras | Terikat `alp_id` |
| BR-007 | Melebihi baki ditolak | Selaras | Ledger Available − Pending |

### Klausa Hybrid (masuk URS v1.1)

> Polisi sumbangan BR-001…004 adalah **pilihan konfigurasi**. Apabila dimatikan, sistem membenarkan aliran projek/ledger tanpa had RM30k/RM3k/kuota tempoh.

### Tetapan Polisi URS (lalai)

| Tetapan | Kunci DB | Lalai |
|---------|----------|-------|
| Polisi aktif | `urs_policy_enabled` | `false` (OFF) |
| Had tahunan | `urs_max_annual_allocation` | 30000.00 |
| Had permohonan | `urs_max_per_application` | 3000.00 |
| Kuota tempoh | `urs_period_quota` | 10000.00 |
| Ambang tertunggak | `urs_overdue_days` | 7 hari |

**Skrin:** `/tetapan/polisi-urs` (kebenaran `settings.manage`)

---

## 6. Analisis peranan

| Peranan URS | RoleName sistem | Fit | Nota |
|-------------|-----------------|-----|------|
| ALP (Pemohon) | `alp` | Selaras | Lihat rekod sendiri sahaja |
| Pegawai JP / Urus Setia | `pegawai_urussetia` | Selaras | Semakan peringkat awal |
| Pengarah JP (Peraku) | `pelulus` (aras matriks 1) | Selaras | Label UI Peraku |
| Pengarah Eksekutif (Pelulus) | `pelulus` / `pengurusan` | Selaras | Mengikut matriks jumlah |
| JKEW / Kerani bayaran | `pegawai_kewangan` | Selaras | `payments.manage` untuk baucar |
| Pentadbir JPM | `system_admin` / `super_admin` | Selaras | Tetapan polisi |
| *(lebihan hybrid)* | `pegawai_teknikal`, `pengurusan` | Lebihan | Semakan teknikal & dashboard eksekutif |

Rujukan penuh: `docs/ROLE_PERMISSION_MATRIX.md`

---

## 7. Keperluan umum UR-001…010

| ID | Keperluan | Status | Bukti |
|----|-----------|--------|-------|
| UR-001 | Permohonan dalam talian | ✅ Dipenuhi | Wizard 6 langkah |
| UR-002 | Pengguna berasaskan peranan | ✅ Dipenuhi | Spatie RBAC + Policies |
| UR-003 | Maklumat + dokumen | ✅ Dipenuhi | Wizard + `document_requirements` |
| UR-004 | Semakan, perakuan, kelulusan | ✅ Dipenuhi | 3 semakan + matriks |
| UR-005 | Semak baki peruntukan | ✅ Dipenuhi | Ledger + Pending Request |
| UR-006 | Dashboard & laporan | ✅ Dipenuhi | Dashboard + modul laporan |
| UR-007 | Notifikasi | ✅ Dipenuhi | In-app + e-mel |
| UR-008 | Audit trail | ✅ Dipenuhi | `audit_logs` |
| UR-009 | Status pembayaran | ✅ Dipenuhi | Modul baucar |
| UR-010 | Web + mesra mudah alih | ⚠ Separa | Web responsif; app asli luar skop (OS-04) |

---

## 8. Perkara luar skop (Bahagian 6)

| ID | Perkara | Sebab |
|----|---------|-------|
| OS-01 | Integrasi ERP / e-baucar DBKL | Rekod manual no. baucar |
| OS-02 | GIRO/FPX automatik | Status & rujukan sahaja |
| OS-03 | Self-register ALP | Akaun oleh pentadbir |
| OS-04 | App iOS/Android asli | Web responsif |
| OS-05 | Portal awam tanpa login | Autentikasi wajib |
| OS-06 | Inventori / aset fizikal | Di luar domain peruntukan |
| OS-07 | Penggajian / elaun ALP | — |
| OS-08 | Penukaran mata wang | RM sahaja |
| OS-09 | E-meterai / e-sign sah | Surat cetak sahaja |
| OS-10 | Arkib eDokumen DBKL | Muat naik dalam sistem |

Item di atas dimasukkan dalam URS v1.1 Word. Sebarang penambahan skop memerlukan **CRS/URS tambahan**.

---

## 9. Keputusan cadangan P01–P17

*(Menunggu tandatangan pemilik proses — tandakan S/T)*

| ID | Isu | Cadangan | S/T |
|----|-----|----------|-----|
| P01 | Skop rasmi | **Hybrid** — polisi URS pilihan | ☐ |
| P02 | Semakan Kewangan & Teknikal | **Dalam skop** — kembangkan M4 | ☐ |
| P03 | Self-register ALP | **Luar skop** (OS-03) | ☐ |
| P04 | Had kuasa pelulus | **Matriks kelulusan** boleh dikonfig | ☐ |
| P05 | Medan wajib borang | Kekalkan wizard semasa | ☐ |
| P06 | Dokumen wajib | `document_requirements` | ☐ |
| P07 | Notifikasi e-mel | **Ya** — DB + mail | ☐ |
| P08 | SLA tertunggak | Ambang hari boleh dikonfig (lalai 7) | ☐ |
| P09 | Surat kelulusan | Template sistem; letterhead kemudian | ☐ |
| P10 | Integrasi baucar DBKL | **Luar skop** (OS-01) | ☐ |
| P11 | CSV pembayaran | **Dalam skop** | ☐ |
| P12 | Polisi lalai ON/OFF | **OFF** (UAT/projek besar) | ☐ |
| P13 | Tahun kewangan vs tempoh | Guna `financial_years.year` | ☐ |
| P14 | Baucar vs belanja | **Berasingan** | ☐ |
| P15 | Label Peraku RBAC | Label/matriks; tiada role baharu | ☐ |
| P16 | App mudah alih | **Luar skop** (OS-04) | ☐ |
| P17 | Perubahan BR selepas go-live | Tetapan Polisi URS + audit | ☐ |

---

## 10. Pelaksanaan Fasa A–D (ringkasan teknikal)

### Fasa A — Polisi, notifikasi, surat kelulusan

| Ciri | Lokasi |
|------|--------|
| Polisi URS | `app/Support/UrsContributionPolicy.php` |
| Tetapan | `SystemSettingController` · `/tetapan/polisi-urs` |
| Notifikasi | `ApplicationNotifier` · `/notifikasi` · loceng header |
| Surat kelulusan | `ApplicationController@letter` · `/permohonan/{id}/surat-kelulusan` |
| Migration | `2026_08_25_120000_create_system_settings_and_notifications_tables.php` |
| Ujian | `tests/Feature/UrsPolicyAndNotificationTest.php` (7 ujian) |

### Fasa B — Tempoh, tertunggak, label Peraku

| Ciri | Lokasi |
|------|--------|
| Tempoh 4-bulan | `UrsContributionPolicy::periodFor()` |
| Kuota tempoh + luput | BR-003/004 dalam policy + submission |
| Dashboard tertunggak | `DashboardController` |
| Label Peraku/Pelulus | `ApplicationStatus`, `RoleName`, view kelulusan |
| Ujian | `tests/Feature/UrsPeriodAndOverdueTest.php` (5 ujian) |

### Fasa C — Modul baucar/pembayaran

| Ciri | Lokasi |
|------|--------|
| Enum status | `ApplicationPaymentStatus` |
| Perkhidmatan | `ApplicationPaymentService` |
| Controller | `PaymentController` |
| Laluan | `/pembayaran`, `/pembayaran/eksport`, `payments.update` |
| Kebenaran | `payments.view`, `payments.manage` |
| Medan DB | `payment_status`, `voucher_number`, `paid_at`, dll. |
| Ujian | `tests/Feature/ApplicationPaymentTest.php` (6 ujian) |

**Keputusan reka bentuk Fasa C:** Status baucar pada `Application`, **bukan** dipetakan ke `ProjectExpense` — mengelakkan double-count dengan EXPENDITURE.

### Fasa D — Dokumentasi URS v1.1

| Hasil | Lokasi |
|-------|--------|
| Addendum MD | `docs/URS_ADDENDUM_HYBRID_v1.1.md` |
| Panduan Word | `docs/URS_PANDUAN_KEMASKINI_v1.1.md` |
| URS Word v1.1 | `URS/..._Standard_v1.1.docx` |
| Skrip jana | `scripts/build_urs_v1_1.php` |
| Canvas analisis | `canvases/urs-vs-sistem.canvas.tsx` |

---

## 11. Rujukan teknikal — URL & fail utama

### URL penting (Hybrid)

| Fungsi | URL | Peranan tipikal |
|--------|-----|-----------------|
| Dashboard | `/dashboard` | Semua |
| Polisi URS | `/tetapan/polisi-urs` | System Admin |
| Notifikasi | `/notifikasi` | Semua (menerima) |
| Surat kelulusan | `/permohonan/{id}/surat-kelulusan` | Pelulus, Urus Setia, ALP (sendiri) |
| Giliran baucar | `/pembayaran` | Pegawai Kewangan, Pengurusan |
| Eksport CSV baucar | `/pembayaran/eksport` | Pegawai Kewangan |
| Semakan Urus Setia | `/semakan/urus-setia` | Pegawai Urus Setia |
| Semakan Kewangan | `/semakan/kewangan` | Pegawai Kewangan |
| Semakan Teknikal | `/semakan/teknikal` | Pegawai Teknikal |
| Kelulusan | `/kelulusan` | Pelulus, Pengurusan |

### Persekitaran pembangunan

| Item | Nilai |
|------|-------|
| URL tempatan | `http://localhost/alp/public/` |
| Pangkalan data | `alp_dbkl` |
| DB ujian | `alp_dbkl_test` |
| PHP | `C:\xampp2\php\php.exe` |
| Log masuk dev | `superadmin@dbkl.test` / `password` |

### Akaun UAT (DevSeeder)

`superadmin@dbkl.test`, `sysadmin@dbkl.test`, `alp01@dbkl.test`, `urussetia@dbkl.test`, `kewangan@dbkl.test`, `teknikal@dbkl.test`, `pelulus@dbkl.test`, `pengurusan@dbkl.test` — kata laluan: `password`

---

## 12. Panduan UAT — dua mod operasi

UAT Hybrid **mesti** merangkumi kedua-dua mod Polisi URS.

### Mod 1: Polisi URS OFF (lalai)

**Bila guna:** UAT projek besar, CSR/pembangunan, aliran korporat.

| Senario | Langkah | Jangkaan |
|---------|-------|----------|
| Permohonan > RM3,000 | ALP hantar permohonan RM5,000 | ✅ Diterima (tiada had BR-002) |
| Tiada kuota tempoh | Hantar beberapa permohonan dalam tempoh sama | ✅ Tiada sekatan tempoh |
| Dashboard | Buka dashboard ALP | Tiada kad kuota tempoh |
| Aliran projek | Lulus → projek → belanja → verify | Ledger EXPENDITURE seperti biasa |
| Baucar | Kemas kini status di `/pembayaran` | Metadata sahaja; tiada EXPENDITURE |

### Mod 2: Polisi URS ON

**Bila guna:** UAT operasi sumbangan ketat selaras URS.

| Senario | Langkah | Jangkaan |
|---------|-------|----------|
| Aktifkan polisi | System Admin → `/tetapan/polisi-urs` → ON | Tetapan disimpan |
| Had permohonan | ALP hantar > RM3,000 | ❌ Ditolak dengan mesej BM |
| Had dalam had | ALP hantar ≤ RM3,000 | ✅ Diterima |
| Kuota tempoh | Hantar melebihi RM10k dalam tempoh sama | ❌ Ditolak (BR-003) |
| Tiada carry-forward | Guna kuota tempoh 1; cuba baki di tempoh 2 | Baki tempoh 1 tidak dibawa |
| Tertunggak | Permohonan SUBMITTED > 7 hari | Muncul dalam dashboard tertunggak |
| Notifikasi | Hantar / lulus / tolak / baucar | Notifikasi in-app + e-mel |
| Surat kelulusan | Buka permohonan diluluskan → cetak | PDF/HTML surat kelulusan |
| Baucar | Kewangan: Disedia → Dibayar + no. baucar | Status dikemas kini; notifikasi |

### Senario integriti kewangan (kedua-dua mod)

| Senario | Jangkaan |
|---------|----------|
| Baucar ditandakan Dibayar | **Tiada** transaksi EXPENDITURE baharu |
| Belanja projek disahkan | **Satu** transaksi EXPENDITURE |
| `php artisan integrity:check` | 0 pengecualian |

---

## 13. Aliran operasi harian (panduan pengguna)

### ALP

1. Log masuk → Dashboard / Bajet Saya  
2. Permohonan Baharu → wizard (Maklumat → Objektif → Bajet → Dokumen → Semakan)  
3. Hantar → tunggu notifikasi status  
4. Jika REVISION_REQUIRED → betulkan → hantar semula  
5. Selepas diluluskan → lihat surat kelulusan; pantau status baucar  

### Urus Setia DBKL

1. Semak giliran `/semakan/urus-setia`  
2. RECOMMEND / RETURN_FOR_REVISION  
3. Urus kemajuan projek, milestone, dokumen penutupan  

### Pegawai Kewangan

1. Semak giliran `/semakan/kewangan`  
2. Rekod & sahkan perbelanjaan projek (Aliran B)  
3. Kemas kini status baucar `/pembayaran` (Aliran A)  
4. Eksport CSV pembayaran jika perlu  

### Pelulus / Peraku

1. Giliran `/kelulusan`  
2. Lulus / Tolak / Kembalikan  
3. Sahkan perbelanjaan & refund (checker)  

### System Admin

1. Urus pengguna, ALP, tahun kewangan, matriks kelulusan  
2. Konfigurasi **Polisi URS** mengikut mod operasi  
3. Pantau tetapan SMTP untuk notifikasi e-mel production  

---

## 14. Senarai semak

### Pemilik proses (JPM)

- [ ] Semak & tandatangan P01–P17 (§9 panduan ini)
- [ ] Sahkan Bah. 6 (OS-01…10) dalam URS Word v1.1
- [ ] Kemas kini Senarai Kandungan Word
- [ ] Sahkan M4 (3 semakan) dan M6 (baucar) dalam teks URS
- [ ] Tentukan mod lalai production: Polisi ON atau OFF
- [ ] Tandatangan §17 URS v1.1 sebagai dokumen rasmi

### Pasukan teknikal

- [ ] Jalankan UAT Mod OFF (`docs/UAT_CHECKLIST.md` + senario §12)
- [ ] Jalankan UAT Mod ON (senario §12)
- [ ] Sahkan SMTP production untuk e-mel notifikasi
- [ ] `php artisan migrate` + seed tetapan (`SystemSettingSeeder`, `RolePermissionSeeder`)
- [ ] `php artisan integrity:check` = 0
- [ ] Suite ujian penuh sebelum go-live

### Pentadbir deployment

- [ ] `APP_DEBUG=false`, HTTPS, backup DB + `storage/app/private`
- [ ] Rujuk `docs/DEPLOYMENT_READINESS.md` dan `docs/SECURITY_CHECKLIST.md`

---

## 15. Risiko & had diketahui

| Risiko / Had | Kesan | Mitigasi |
|--------------|-------|----------|
| Polisi OFF lalai | Operasi mungkin tidak selaras BR URS ketat | Aktifkan ON untuk mod sumbangan; dokumentasi jelas |
| Baucar manual | Ralat no. baucar / status | Audit trail; CSV eksport; latihan JKEW |
| Dwi-aliran | Kekeliruan baucar vs belanja | Latihan; panduan ini; label UI jelas |
| E-mel sync | Notifikasi e-mel lambat/gagal tanpa queue | Konfigur SMTP; pertimbang queue production |
| Word v1.1 auto-generated | Formatting mungkin perlu kemas manual | Semak dengan `URS_PANDUAN_KEMASKINI_v1.1.md` |
| P01–P17 belum ditandatangani | URS v1.1 kekal cadangan | Kejar pengesahan pemilik proses |
| Integriti dashboard O(projek) | Perlahan pada skala besar | Cache/cron — lihat `DEPLOYMENT_READINESS.md` |

---

## 16. Langkah seterusnya

1. **Pemilik proses:** Sahkan P01–P17 dan terbitkan URS v1.1 rasmi.  
2. **UAT:** Jalankan checklist dual-mode (OFF + ON).  
3. **Operasi:** Tentukan bila Polisi URS dihidupkan untuk operasi sumbangan sebenar.  
4. **Production readiness:** Selesaikan item ACTION dalam security/deployment checklist.  
5. **CRS masa depan:** Jika OS-01/OS-03/OS-04 dimasukkan skop — buka CRS baharu.

---

## 17. Arahan pantas pasukan teknikal

```powershell
# Setup
cd C:\xampp2\htdocs\alp
composer install
npm install
php artisan migrate --seed

# Ujian Hybrid
C:\xampp2\php\php.exe vendor\bin\phpunit --filter "UrsPolicy|UrsPeriod|ApplicationPayment"

# Jana semula URS Word v1.1
C:\xampp2\php\php.exe scripts\build_urs_v1_1.php
C:\xampp2\php\php.exe scripts\verify_urs_v1_1.php

# Integriti kewangan
php artisan integrity:check
```

---

**Penyediaan:** Pasukan pelaksanaan Sistem ALP DBKL (Analisis Hybrid v1.1)  
**Kemas kini:** Kemaskini panduan ini apabila P01–P17 disahkan atau skop berubah.
