# Panduan Analisis — URS v1.2 vs Sistem ALP DBKL

**Versi panduan:** 1.0  
**Tarikh analisis:** 5 September 2026  
**Sumber URS:** `URS/URS_Sistem_Pengurusan_Sumbangan ALP DBKL_v1_2_5926.docx`  
**Status URS:** Versi **1.2** · Draf untuk Semakan dan Pengesahan · Tarikh kawalan **28.08.2026** · Pindaan JP **02.09.2026**  
**Pemilik proses:** Jabatan Pentadbiran, DBKL  
**Sistem bandingan:** Laravel ALP DBKL (UAT Ready + Hybrid Fasa A–D)

---

## Indeks dokumen berkaitan

| Dokumen | Lokasi |
|---------|--------|
| **Panduan ini (URS v1.2)** | `docs/PANDUAN_ANALISIS_URS_v1.2.md` |
| **System Inventory (Fasa 2)** | `docs/URS_v1.2_SYSTEM_INVENTORY.md` |
| **Gap Analysis & Klasifikasi (Fasa 3–4)** | `docs/URS_v1.2_GAP_ANALYSIS.md` |
| **URS Traceability** | `docs/URS_TRACEABILITY.md` |
| **Pelan Refactoring** | `docs/URS_v1.2_REFACTORING_PLAN.md` |
| **Database Cleanup Proposal** | `docs/URS_v1.2_DATABASE_CLEANUP_PROPOSAL.md` |
| **Legacy Components** | `_reference_legacy/documentation/LEGACY_COMPONENTS.md` |
| URS Word v1.2 (rasmi terkini) | `URS/URS_Sistem_Pengurusan_Sumbangan ALP DBKL_v1_2_5926.docx` |
| Analisis Hybrid (v1.0/v1.1) — rujukan sejarah | `docs/PANDUAN_ANALISIS_URS_HYBRID.md` |
| Panduan login UAT | `docs/PANDUAN_LOGIN.md` |

> **Nota:** URS v1.2 ialah **Single Source of Truth**. Dokumen Hybrid/URS lama disimpan sebagai sejarah sahaja. Realignment aktif: lihat Gap Analysis & Pelan Refactoring.

---

## 1. Ringkasan eksekutif

### Apa yang berubah dalam v1.2

URS v1.2 ialah dokumen **sumbangan ALP berfokus JP** (bukan platform projek korporat). Perubahan utama berbanding pemahaman Hybrid v1.1:

| Aspek | Hybrid / URS lama | URS v1.2 |
|-------|-------------------|----------|
| Tajuk | Permohonan & Peruntukan | **Pengurusan Sumbangan** |
| BR | 7 peraturan (ID lama) | **BR-001 … BR-023** (ID disusun semula + baharu) |
| Peranan | Ringkas + lebihan sistem | **Matriks TBL-04**: Admin JP, Kerani Kewangan JP, Pegawai JP, TP/Pengarah, PEPU, JKEW |
| Modul | M1–M10 | **M01–M11** (+ Manual/Kit) |
| Luar skop / Hybrid | OS-01…10 + dwi-aliran | **Tiada seksyen luar skop**; tiada dwi-aliran baucar vs belanja |
| Polisi ON/OFF | Pilihan (lalai OFF) | BR dibaca sebagai **wajib** (tiada suis OFF) |
| KPI | Tertunggak hari | **14 hari** hingga hantar dokumen ke JKEW |

### Verdict fit keseluruhan

| Status | Bilangan | Makna |
|--------|----------|-------|
| **Selaras** | 2 modul (M08, M09) | Sudah memenuhi keperluan utama |
| **Separa** | 8 modul (M01–M07, M10 + Notifikasi) | Ada asas; jurang medan/aliran/peranan |
| **Gap** | 1 modul (M11) + banyak BR baharu | Perlu pembangunan / CRS |
| **Lebihan sistem** | Ledger, projek, belanja, refund, semakan 3 peringkat | Ada dalam sistem; **tidak** dalam URS v1.2 |

**Intipati:** Sistem Laravel ialah **superset Hybrid**. Wang asas (RM30k / tempoh / RM3k) boleh dipenuhi bila Polisi URS **ON**. Jurang besar ialah **persatuan sekali/tahun**, **kelayakan ikut lantikan**, **borang & dokumen JP**, **aliran JKEW**, **timeline 14 hari**, **report card**, **M11 manual**, dan **4 tahap JP + JKEW**.

---

## 2. Metadata & struktur URS v1.2

| Perkara | Butiran |
|---------|---------|
| Nama penuh | Dokumen Keperluan Pengguna (URS) — Sistem Pengurusan Sumbangan ALP Bandaraya Kuala Lumpur |
| Versi | 1.2 |
| Status | Draf untuk Semakan dan Pengesahan |
| Tarikh | 28.08.2026 (kawalan); pindaan 02.09.2026 |
| Rujukan | Slaid cadangan; carta alir; senarai semak; Borang Penyaluran Sumbangan ALP 2026 |
| Bahagian | 1–15 (Pengenalan → Pengesahan) |

### Rekod pindaan ringkas

| Versi | Tarikh | Ringkasan |
|-------|--------|-----------|
| 1.0 | 27 Ogos 2026 | Format URS standard |
| 1.1 | 27 Ogos 2026 | Permission matrix + dashboard diperkaya |
| 1.2 | 02 Sep 2026 | Pembetulan semakan Jabatan Pentadbiran |

---

## 3. Aliran kerja URS v1.2 (sasaran)

```
ALP hantar permohonan
  → Pegawai JP semak (kelengkapan, syarat, baki)
  → TP / Pengarah JP peraku (disyorkan / tidak / pindaan)
  → PEPU / Pengurusan Tertinggi lulus / tolak / pindaan
  → Kerani Kewangan JP rekod baucar + hantar ke JKEW
  → JKEW semak + semakan silang JPKKB + bayaran
  → Status bayaran dikemas kini → notifikasi ALP
  → ALP muat naik laporan aktiviti / report card
```

### Bandingan dengan sistem semasa

| Langkah URS | Sistem semasa | Fit |
|-------------|---------------|-----|
| Semakan Pegawai JP | Semakan Urus Setia (+ Kewangan + Teknikal) | Lebihan / Separa |
| Peraku TP/Pengarah | Matriks aras 1 + label Peraku | Separa |
| Pelulus PEPU | `pelulus` / `pengurusan` | Separa |
| Baucar JP | `/pembayaran` (metadata) | Separa |
| Hantar ke JKEW + silang JPKKB | Tiada medan/proses khusus | **Gap** |
| Report card 1 bulan | Laporan projek (separa) | Separa |

---

## 4. Pemetaan modul M01–M11

| Modul | Keperluan URS (ringkas) | Sistem | Fit | Jurang utama |
|-------|-------------------------|--------|-----|--------------|
| **M01** Login & pengguna | E-mel login; urus peranan; kelayakan ikut lantikan | Auth + Spatie RBAC | Separa | Tiada Admin JP / Kerani / JKEW berasingan; BR-007 tidak dikuatkuasa |
| **M02** Dashboard | Timeline ke JKEW; KPI 14 hari; penapis Admin JP; graf; tertunggak | Dashboard + overdue + kuota | Separa | Timeline & KPI 14 hari belum; penapis Admin JP belum lengkap |
| **M03** Permohonan | Borang A–P; had RM3k; dokumen TBL-9; 1 persatuan | Wizard 6 langkah + polisi | Separa | Medan penerima/ROS/akaun bank; jenis program URS; BR-008…015 |
| **M04** Semakan | Checklist JP → hantar ke peraku | 3 semakan (US/Kewangan/Teknikal) | Lebihan + Separa | Checklist item-by-item URS; label “hantar ke TP” |
| **M05** Kelulusan | Peraku lalu PEPU; DISYORKAN/TIDAK | Matriks + surat kelulusan | Separa | Nama peranan PEPU; letterhead JP |
| **M06** Pembayaran | Baucar; tarikh hantar JKEW; silang; status bayaran | `/pembayaran` + CSV | Separa | `sent_to_jkew_at`; BR-013; skop JKEW |
| **M07** Laporan program | Report card; SLA 1 bulan; NT-007 | Laporan akhir projek | Separa | Templat report card; peringatan |
| **M08** Laporan & analisis | Bulan/tahun/ALP/status/baki + eksport | Modul laporan Fasa 6 | **Selaras** | Istilah “sumbangan” vs ledger |
| **M09** Jejak audit | Tindakan utama + pengguna + masa | `audit_logs` | **Selaras** | — |
| **M10** Tetapan | Had siling dinamik; templat surat/laporan/borang | Polisi URS + FY + matriks | Separa | Peranan Admin JP; templat borang |
| **M11** Manual / kit | Manual ikut peranan | Tiada | **Gap** | Modul baharu |
| **Notifikasi** | NT-001…007 | In-app + e-mel | Separa→Selaras | Sasaran peranan tepat; NT-007 cadangan |

### Lebihan sistem (ada tetapi di luar naratif URS v1.2)

- Ledger append-only + maker-checker peruntukan  
- Modul projek, perbelanjaan, refund, penutupan  
- Semakan Teknikal  
- Dashboard eksekutif / kewangan korporat  
- Polisi URS boleh dimatikan (Hybrid)

---

## 5. Peraturan perniagaan BR-001…023

### 5.1 Pemetaan ID lama → v1.2 (penting)

| Konsep | ID Hybrid lama | **ID URS v1.2** |
|--------|----------------|-----------------|
| Max RM30,000 / tahun | BR-001 | **BR-001** |
| 3 tempoh × RM10,000 | BR-003 | **BR-002** |
| Baki tempoh luput | BR-004 | **BR-003** |
| Tidak boleh kongsi ALP | BR-006 | **BR-004** |
| Max RM3,000 / permohonan | BR-002 | **BR-005** |
| Tiada had bilangan | BR-005 | **BR-021** |
| Melebihi baki ditolak | BR-007 | **BR-022** |

Kod `UrsContributionPolicy` masih komen ID lama — perlu dikemas kini.

### 5.2 Status pelaksanaan

| ID | Peraturan | Status sistem |
|----|-----------|---------------|
| BR-001 | RM30,000 / ALP / tahun | ✅ Bila Polisi ON |
| BR-002 | 3 × RM10,000 (4 bulan) | ✅ Bila Polisi ON |
| BR-003 | Tiada bawa ke hadapan | ✅ Bila Polisi ON |
| BR-004 | Tidak boleh kongsi ALP | ✅ (`alp_id`) |
| BR-005 | Max RM3,000 / permohonan | ✅ Bila Polisi ON |
| BR-006 | Admin JP had siling dinamik | ⚠ Separa (tetapan ada; peranan Admin JP tiada) |
| BR-007 | Kelayakan ikut tarikh lantikan (cth. Jun = RM20k) | ❌ Gap |
| BR-008 | Satu permohonan = satu persatuan | ❌ Gap |
| BR-009 / BR-023 | Satu persatuan sekali setahun | ❌ Gap (AD-005: kaedah ID belum sah) |
| BR-010 | Alamat berdaftar di KL sahaja | ❌ Gap |
| BR-011 | Jenis dibenarkan: komuniti/sukan/pendidikan/kemasyarakatan | ❌ Gap |
| BR-012 | Larangan: pentadbiran/politik/perayaan/agama | ❌ Gap |
| BR-013 | Semakan silang JKEW × JPKKB | ❌ Gap (AD-003) |
| BR-014 / BR-015 | Hantar ≥ 2 bulan sebelum program | ❌ Gap |
| BR-016 | Checklist dokumen mandatori | ⚠ Separa (dokumen ada; set TBL-9 berbeza) |
| BR-017 | Kekalkan kandungan borang sedia ada | ⚠ Separa (wizard ≠ A–P penuh) |
| BR-018 / BR-019 | Report card dalam 1 bulan | ⚠ Separa |
| BR-020 | Cetak borang + surat + laporan | ⚠ Separa (surat & laporan ada; borang penyaluran belum) |
| BR-021 | Tiada had bilangan permohonan | ✅ |
| BR-022 | Melebihi baki ditolak | ✅ |

### 5.3 Polisi ON vs OFF vs URS v1.2

| Mod | Sesuai URS v1.2? | Catatan |
|-----|------------------|---------|
| Polisi **ON** | Ya (wang BR-001/002/003/005) | Disyorkan untuk operasi sumbangan ketat / UAT AC-003/004 |
| Polisi **OFF** (lalai sistem) | **Tidak** selaras AC wang | Kekal untuk projek CSR besar (Hybrid) — **perlu keputusan pemilik** |

---

## 6. Peranan & permission matrix

| Peranan URS v1.2 | Role sistem terdekat | Fit | Tindakan |
|------------------|----------------------|-----|----------|
| ALP | `alp` | Selaras | — |
| Admin JP | ≈ `system_admin` | Separa | Pertimbang role `admin_jp` atau alias jelas |
| Kerani Kewangan JP | ≈ `pegawai_kewangan` | Separa | Pisahkan baucar JP vs belanja projek jika perlu |
| Pegawai JP | `pegawai_urussetia` | Separa | — |
| TP/Pengarah JP (Peraku) | `pelulus` aras 1 | Separa | Label OK; role khusus pilihan |
| PEPU / Pengurusan Tertinggi | `pelulus` / `pengurusan` | Separa | — |
| Pengguna JKEW | ≈ kewangan (payments) | **Gap** | Skop “hanya rekod dihantar ke JKEW” (SEC-007) |
| Pentadbir Sistem JP/JPM | `system_admin` / `super_admin` | Selaras | — |
| *(lebihan)* Pegawai Teknikal | `pegawai_teknikal` | Lebihan | Kekal Hybrid |

Rujukan matriks sistem: `docs/ROLE_PERMISSION_MATRIX.md`  
Rujukan matriks URS: TBL-04 dalam Word v1.2.

---

## 7. Dokumen mandatori & medan borang

### TBL-9 — Dokumen wajib URS

1. Salinan Pendaftaran Pertubuhan/Organisasi  
2. Borang Maklumat EFT  
3. Salinan Penyata Bank Muka Hadapan  
4. Kertas Kerja  
5. Dokumen/Sijil ROS (sah daftar)

### TBL-10 — Medan borang (A–P) yang perlu disokong

Nama ALP · Nama penerima · Jumlah · Tujuan · No. akaun · Ruang JP (peruntukan/perbelanjaan/baki) · Perakuan · Keputusan DISYORKAN/TIDAK · Tarikh program · Jenis program · No. ROS · Alamat persatuan

**Sistem semasa:** wizard maklumat/objektif/bajet/dokumen — **belum** padan penuh A–P + TBL-9.

---

## 8. Notifikasi NT-001…007

| ID | Peristiwa | Sistem |
|----|-----------|--------|
| NT-001 | Hantar berjaya | ✅ |
| NT-002 | Kembalikan / pindaan | ✅ |
| NT-003 | Menunggu perakuan | ⚠ (notifikasi ada; sasaran “TP/Pengarah” perlu disahkan) |
| NT-004 | Menunggu kelulusan PEPU | ⚠ |
| NT-005 | Keputusan lulus/tolak | ✅ |
| NT-006 | Status pembayaran | ✅ |
| NT-007 | Peringatan report card | ❌ Cadangan URS — belum |

---

## 9. Keperluan umum UR-001…012 (v1.2)

| ID | Ringkas | Status |
|----|---------|--------|
| UR-001 | Log masuk / pengguna | Separa |
| UR-002… | Baki & had siling | Separa (bila ON) |
| … | Satu persatuan / tahun | Gap |
| … | Checklist | Separa |
| … | Status + inbox/emel | Separa→Selaras |
| … | Audit | Selaras |
| … | Laporan | Selaras |
| UR-010 | KPI 14 hari ke JKEW | Gap |
| … | Report card | Separa |
| UR-012 | Manual pengguna | Gap (M11) |

*(Butiran penuh dalam Word Bah. 8.)*

---

## 10. Kriteria penerimaan AC — siap sedia UAT?

| AC | Ringkas | Siap UAT? | Nota (selepas residual) |
|----|---------|-----------|-------------------------|
| AC-001 | ALP aktif boleh mohon | Ya | Wizard + submit |
| AC-002 | Satu persatuan / permohonan | Ya | Entiti `recipients` + medan TBL-10 |
| AC-003 | Halang > RM3,000 | Ya **jika Polisi ON** | Lalai ON |
| AC-004 | Tempoh RM10k + luput | Ya **jika Polisi ON** | |
| AC-005 | Tidak kongsi ALP | Ya | |
| AC-006 | Kelayakan ikut lantikan | Separa | BR-007 proration linear |
| AC-007 | Checklist lengkap | Ya | TBL-9 + senarai semak JP UR-M04-001 |
| AC-008 | Halang pendua persatuan/tahun | Ya | ROS unik/tahun (`recipient_id`) |
| AC-009 | JP pulangkan + ulasan | Ya | Pegawai JP sahaja |
| AC-010 | Aliran peraku/pelulus direkod | Ya | ≤3k Peraku; >3k PEPU |
| AC-011 | Dashboard timeline + JKEW | Separa | Timeline + KPI 14 hari |
| AC-012 | Semakan silang JKEW | Separa | Rekod manual baucar |
| AC-013 | Status bayaran + notifikasi | Ya (asas) | `/pembayaran` |
| AC-014 | Muat naik report card | Separa | Tab + `/laporan-aktiviti` |
| AC-015 | Jana laporan | Ya | Tanpa laporan projek legacy |
| AC-016 | Audit | Ya | |
| AC-017 | Permission matrix | Separa | Label URS; value Spatie (+ role JKEW) |
| AC-018 | Cetak borang + surat | Ya | Surat/borang + templat M10 |
| AC-019 | Berbilang permohonan / persatuan berbeza | Ya | ROS berbeza dibenarkan |
| AC-020 | Penapis Admin JP | Separa | |
| AC-021 | Skop data ALP / JKEW / admin | Ya (asas) | Role `pegawai_jkew` + skop pembayaran |

Checklist operasi: [UAT_CHECKLIST.md](UAT_CHECKLIST.md).

---

## 11. Risiko utama

| Risiko | Kesan | Mitigasi |
|--------|-------|----------|
| Dua “kebenaran” (URS v1.2 vs Hybrid) | UAT/kelulusan bercanggah | Pemilik proses sahkan: v1.2 sahaja **atau** v1.2 + Addendum Hybrid |
| Polisi OFF lalai | Gagal AC-003/004 | Hidupkan ON untuk UAT sumbangan; dokumentasikan dual-mode |
| BR ID berubah | Kekeliruan kod/dokumen | Kemas komen policy + panduan |
| Model persatuan tiada | BR-008/009/023 tidak boleh UAT | CRS entiti penerima + unik tahunan |
| JKEW / JPKKB | M06 & AC-012 tidak lengkap | CRS medan + skop role; tunggu AD-003 |
| M11 tiada | Gagal UR-012 / AC manual | Muat naik PDF manual sebagai Fasa pantas |

---

## 12. Cadangan roadmap (selepas v1.2)

### Keutamaan A — keputusan pemilik (tanpa kod)

1. Sahkan dokumen asas: **URS v1.2 sahaja** atau **+ Hybrid Addendum**.  
2. Sahkan Polisi URS production: **ON** (sumbangan ketat) vs dual-mode.  
3. Sahkan AD-002 (data lantikan), AD-003 (proses JPKKB), AD-005 (ID persatuan: ROS? nama?).  
4. Sahkan PV-001 (KPI 14 hari).  
5. Map TBL-04 → role Spatie (cipta role baharu atau alias rasmi).

### Keutamaan B — CRS pembangunan (jurang fungsi)

| # | Item | BR / AC |
|---|------|---------|
| 1 | Model penerima/persatuan + unik sekali/tahun | BR-008/009/023, AC-002/008/019 |
| 2 | Medan borang A–P + dokumen TBL-9 | BR-016/017, AC-007/018 |
| 3 | Kelayakan ikut tarikh lantikan | BR-007, AC-006 |
| 4 | Peraturan program (jenis, KL, 2 bulan) | BR-010…015 |
| 5 | Aliran JKEW (tarikh hantar, silang, skop data) | BR-013, M06, AC-012/021 |
| 6 | Dashboard timeline + indikator 14 hari | M02, UR-010, AC-011 |
| 7 | Report card + peringatan NT-007 | M07, BR-018/019 |
| 8 | Modul Manual M11 | UR-012 |
| 9 | Role Admin JP / Kerani / JKEW (jika diminta) | TBL-04, SEC-007 |

### Keutamaan C — housekeeping

- Kemas `UrsContributionPolicy` komen → ID BR v1.2  
- Kemas `docs/PANDUAN_ANALISIS_URS_HYBRID.md` dengan nota “diganti/dirujuk oleh v1.2”  
- Semak README (notifikasi sudah wujud)  
- UAT checklist baharu ikut AC-001…021  

---

## 13. Apa yang sudah baik (jangan buang)

Sistem sudah mempunyai asas kukuh yang **melebihi** URS sumbangan semata:

- Ledger tepat (Money/BCMath), maker-checker, audit  
- Wizard permohonan + semakan + matriks kelulusan  
- Notifikasi in-app + e-mel  
- Modul baucar asas + surat kelulusan  
- Polisi URS boleh dikonfig (had, tempoh, tertunggak)  
- Laporan & eksport Fasa 6  
- Hardening Fasa 7 (UAT ready)

**Cadangan strategik:** Kekalkan Hybrid sebagai platform; **selaraskan permukaan sumbangan** (borang, persatuan, JKEW, BR baharu) kepada URS v1.2 tanpa membuang modul projek.

---

## 14. Arahan teknikal pantas

```powershell
cd C:\xampp2\htdocs\alp

# Log masuk UAT
# http://localhost/alp/public/login
# superadmin@dbkl.test / password

# Hidupkan polisi untuk UAT BR wang
# → /tetapan/polisi-urs → ON

# Ujian Hybrid sedia ada
C:\xampp2\php\php.exe vendor\bin\phpunit --filter "UrsPolicy|UrsPeriod|ApplicationPayment"

# Ekstrak semula teks URS (jika Word dikemas kini)
C:\xampp2\php\php.exe scripts\extract_urs_v12.php
```

---

## 15. Keputusan yang diminta daripada pemilik proses

| # | Soalan | Cadangan teknikal |
|---|--------|-------------------|
| 1 | Adakah URS v1.2 dokumen rasmi tunggal? | Ya untuk skop sumbangan; lampirkan Addendum Hybrid untuk platform |
| 2 | Polisi URS lalai production? | ON untuk operasi sumbangan |
| 3 | Kekalkan semakan 3 peringkat? | Ya (lebihan berguna) — nyatakan dalam URS/CRS |
| 4 | Kekalkan modul projek/belanja? | Ya — di luar skop sumbangan tetapi dalam platform |
| 5 | Kaedah ID persatuan (AD-005)? | Disyorkan: No. ROS |
| 6 | Integrasi JPKKB automatik? | Luar skop awal — rekod manual hasil silang (seperti baucar) |

---

**Disediakan untuk:** Jabatan Pentadbiran / JPM / pasukan pembangunan  
**Disediakan oleh:** Analisis sistem vs URS v1.2 (5 Sep 2026)  
**Sumber teks:** `storage/app/_urs_v12_text.txt` (ekstrak daripada Word)
