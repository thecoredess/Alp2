# UAT Checklist — URS v1.2 (AC-001…021)

**SSOT:** URS v1.2 (bukan Hybrid)  
**URL:** http://localhost/alp/public/  
**Akaun:** lihat [PANDUAN_LOGIN.md](PANDUAN_LOGIN.md) — kata laluan `password`  
**Prasyarat sistem:** Polisi URS **ON** di `/tetapan/polisi-urs` (BR-001/002/003/005)  
**Isu:** rekod dalam [UAT_ISSUE_TEMPLATE.md](UAT_ISSUE_TEMPLATE.md)

**Automasi (5 Sep 2026):** PHPUnit Feature+Unit = **182 lulus** (`--exclude-group legacy`); `php artisan integrity:check` = **0 pengecualian**.  
**Menu aktif:** sidebar tiada pautan projek / belanja / refund / cadangan bajet / dashboard eksekutif.  
**Status:** [URS_v1.2_STATUS.md](URS_v1.2_STATUS.md)

> Checklist legacy (projek, maker-checker belanja, semakan teknikal) diganti oleh dokumen ini. Jangan UAT aliran projek sebagai skop rasmi.

---

## Ringkasan AC

| AC | Kriteria | Sedia UAT? | Senario |
|----|----------|------------|---------|
| AC-001 | ALP aktif boleh mohon | Ya | A1–A3 |
| AC-002 | Satu persatuan / permohonan | Ya | A4 |
| AC-003 | Halang > had permohonan (RM3k jika ON) | Ya jika Polisi ON | A5 |
| AC-004 | Tempoh RM10k + luput | Ya jika Polisi ON | A6 |
| AC-005 | Tidak kongsi bajet ALP | Ya | A7 |
| AC-006 | Kelayakan ikut lantikan (BR-007) | Separa (proration) | B3 |
| AC-007 | Checklist dokumen + semakan JP | Ya | A3, C1 |
| AC-008 | Halang pendua persatuan/tahun (BR-009) | Ya | A8 |
| AC-009 | JP pulangkan + ulasan | Ya | C2 |
| AC-010 | Aliran Peraku/PEPU direkod | Ya | D1–D2 |
| AC-011 | Dashboard timeline + KPI 14 hari | Separa | E1 |
| AC-012 | Semakan silang JKEW | Separa (rekod manual) | F2 |
| AC-013 | Status bayaran + notifikasi | Ya (asas) | F1 |
| AC-014 | Muat naik report card | Separa | G1 |
| AC-015 | Jana laporan | Ya | E2 |
| AC-016 | Audit | Ya | E3 |
| AC-017 | Permission matrix | Separa | H1 |
| AC-018 | Cetak borang + surat | Ya | D3 |
| AC-019 | Berbilang permohonan / persatuan berbeza | Ya | A9 |
| AC-020 | Penapis Admin JP | Separa | E1 |
| AC-021 | Skop data ALP / JKEW / admin | Ya (asas) | H2, F3 |

**Legenda sedia UAT:** Ya = boleh lulus/gagal secara objektif · Separa = ujian boleh dijalankan tetapi jurang diketahui · Belum = tunggu CRS

---

## A. ALP (`alp01@dbkl.test`) — AC-001…005, 007…009, 019

### A1. Log masuk (AC-001 / M01)
- **Precondition:** Akaun ALP aktif.
- **Steps:** `/login` → e-mel + `password`.
- **Expected:** Dashboard; menu: Bajet Saya, Permohonan Saya, Manual, Notifikasi (tiada Projek/Belanja).
- **Pass/Fail:** ___  **Remarks:** ___

### A2. Bajet sendiri (AC-005)
- **Steps:** **Bajet Saya**.
- **Expected:** Hanya peruntukan/komitmen/baki ALP sendiri dari ledger.
- **Pass/Fail:** ___  **Remarks:** ___

### A3. Cipta & hantar permohonan (AC-001, AC-007)
- **Precondition:** Tahun kewangan aktif; peruntukan wujud; Polisi ON.
- **Steps:** Permohonan → Baharu → wizard Maklumat (penerima, ROS, bank, alamat KL, kategori, lokasi, tarikh program ≥2 bulan, deklarasi) → Objektif → Bajet → Dokumen wajib → Semakan → Hantar.
- **Expected:** Status SUBMITTED; baca-sahaja; Pending Request bertambah (**bukan** COMMITMENT).
- **Pass/Fail:** ___  **Remarks:** ___

### A4. Medan penerima / persatuan (AC-002)
- **Steps:** Pada Maklumat, isi `recipient_name`, `recipient_ros_number`, akaun bank, alamat.
- **Expected:** Disimpan; wajib sebelum hantar.
- **Pass/Fail:** ___  **Remarks:** ___

### A5. Halang > RM3,000 (AC-003 / BR-001)
- **Steps:** Bajet item jumlah > had permohonan → Hantar.
- **Expected:** Ditolak dengan mesej polisi (jika Polisi ON).
- **Pass/Fail:** ___  **Remarks:** ___

### A6. Had tempoh RM10k (AC-004 / BR-002)
- **Steps:** Hantar beberapa permohonan dalam tempoh sehingga agregat > had tempoh.
- **Expected:** Hantaran yang melebihi had ditolak; kuota tidak bawa ke tempoh seterusnya.
- **Pass/Fail:** ___  **Remarks:** ___

### A7. IDOR bajet/permohonan (AC-005 / AC-021)
- **Steps:** Sebagai `alp01`, buka URL permohonan milik `alp02` secara langsung.
- **Expected:** 403/404.
- **Pass/Fail:** ___  **Remarks:** ___

### A8. Pendua ROS / tahun (AC-008 / BR-009)
- **Steps:** Hantar permohonan kedua dengan ROS sama dalam tahun kewangan sama.
- **Expected:** Ditolak (unik ROS+tahun).
- **Pass/Fail:** ___  **Remarks:** ___

### A9. Persatuan berbeza (AC-019)
- **Steps:** Hantar permohonan dengan ROS berbeza (dalam had bajet/polisi).
- **Expected:** Dibenarkan jika baki & polisi membenarkan.
- **Pass/Fail:** ___  **Remarks:** ___

### A10. Notis pendek & luar KL (BR-010 / BR-014)
- **Steps:** Alamat luar KL **atau** tarikh program < 2 bulan → Hantar.
- **Expected:** Ditolak / ditanda short notice mengikut polisi.
- **Pass/Fail:** ___  **Remarks:** ___

---

## B. Admin JP (`sysadmin@dbkl.test`) — AC-006, set peruntukan

### B1. Set peruntukan terus (D2 / Fasa 7.12)
- **Steps:** Peruntukan → Cipta / Laras → masukkan jumlah → simpan.
- **Expected:** Allocation + ledger atomik; **tiada** baris Cadangan Bajet / maker-checker.
- **Pass/Fail:** ___  **Remarks:** ___

### B2. Polisi URS (BR-006)
- **Steps:** Polisi URS → sahkan BR-001/002/003/005 ON untuk UAT.
- **Expected:** Nilai disimpan; hantaran ALP mengikut had baharu.
- **Pass/Fail:** ___  **Remarks:** ___

### B3. Kelayakan lantikan (AC-006 / BR-007)
- **Steps:** ALP dengan tarikh lantikan pertengahan tahun → cuba allocate melebihi proration.
- **Expected:** Ditolak jika melebihi siling proration.
- **Pass/Fail:** ___  **Remarks:** ___

---

## C. Pegawai JP (`urussetia@dbkl.test`) — AC-009 / M04

### C1. Recommend
- **Precondition:** Permohonan SUBMITTED.
- **Steps:** Semakan Pegawai JP → tandakan **semua** item senarai semak **Lengkap** → RECOMMEND.
- **Expected:** Status → PENDING_APPROVAL (**bukan** semakan kewangan/teknikal). Tanpa checklist lengkap → ditolak.
- **Pass/Fail:** ___  **Remarks:** ___

### C2. Pulangkan (AC-009)
- **Steps:** Tanda item Tidak lengkap (jika perlu) → RETURN_FOR_REVISION + ulasan.
- **Expected:** REVISION_REQUIRED; ulasan kelihatan kepada ALP; sejarah + checklist direkod.
- **Pass/Fail:** ___  **Remarks:** ___

---

## D. Kelulusan — AC-010, AC-018 / M05

### D1. Peraku ≤ RM3,000 (`pelulus@dbkl.test`)
- **Precondition:** PENDING_APPROVAL, jumlah ≤ RM3,000.
- **Steps:** Kelulusan → Lulus.
- **Expected:** APPROVED; **satu COMMITMENT**; **tiada** rekod projek baharu.
- **Pass/Fail:** ___  **Remarks:** ___

### D2. PEPU > RM3,000 (`pengurusan@dbkl.test`)
- **Precondition:** Jumlah > RM3,000; matriks aras 2 aktif.
- **Steps:** Peraku lulus dahulu (jika berperingkat) → PEPU lulus/tolak.
- **Expected:** Jejak kelulusan lengkap; lulus → COMMITMENT; tolak → REJECTED.
- **Pass/Fail:** ___  **Remarks:** ___

### D3. Cetak surat & borang (AC-018 / BR-020)
- **Steps:** Pada permohonan APPROVED → Surat Kelulusan + Borang Penyaluran.
- **Expected:** Halaman/cetak boleh dibuka; kandungan memuaskan semakan pemilik proses.
- **Pass/Fail:** ___  **Remarks:** ___

---

## E. Dashboard & Laporan — AC-011, AC-015, AC-016, AC-020

### E1. Dashboard KPI / timeline (AC-011, AC-020)
- **Steps:** Dashboard sebagai Admin/JP — penapis, KPI 14 hari, tab Timeline permohonan.
- **Expected:** Senarai tertunggak & status kelihatan; penapis tahun/ALP berfungsi (skop penuh JKEW = residual).
- **Pass/Fail:** ___  **Remarks:** ___

### E2. Laporan (AC-015)
- **Steps:** Laporan → Peruntukan, Ledger, Permohonan (+ eksport jika ada).
- **Expected:** Muat; penapis tahun dihormati; **tiada** menu laporan Projek/CSR legacy.
- **Pass/Fail:** ___  **Remarks:** ___

### E3. Audit (AC-016)
- **Steps:** Laporan → Audit.
- **Expected:** Tindakan utama (hantar, semak, lulus, bayar) direkod.
- **Pass/Fail:** ___  **Remarks:** ___

---

## F. Kerani Kewangan (`kewangan@dbkl.test`) — AC-012, AC-013 / M06

### F1. Baucar & status bayaran (AC-013)
- **Precondition:** Permohonan APPROVED.
- **Steps:** Pembayaran → kemas baucar / status → simpan.
- **Expected:** Status bayaran dikemas; notifikasi asas (jika diaktifkan).
- **Pass/Fail:** ___  **Remarks:** ___

### F2. Hantar JKEW + semakan silang (AC-012)
- **Steps:** Isi `sent_to_jkew_at` / semakan silang JPKKB pada form bayaran; set status Dihantar ke JKEW.
- **Expected:** Medan disimpan; status SENT_TO_JKEW.
- **Pass/Fail:** ___  **Remarks:** ___

### F3. Skop JKEW (`jkew@dbkl.test`) — AC-021 / SEC-007
- **Precondition:** Ada sekurang-kurangnya satu rekod SENT_TO_JKEW dan satu PENDING_PAYMENT.
- **Steps:** Log masuk sebagai `jkew@dbkl.test` → `/pembayaran`.
- **Expected:** Hanya rekod dihantar JKEW kelihatan; tiada `payments.manage` (tidak boleh sunting baucar Kerani).
- **Pass/Fail:** ___  **Remarks:** ___

---

## G. Report card — AC-014 / M07

### G1. Muat naik report card
- **Steps:** Tab Report Card pada permohonan diluluskan **atau** `/laporan-aktiviti` → muat naik.
- **Expected:** Fail diterima; status laporan aktiviti dikemas.
- **Pass/Fail:** ___  **Remarks:** ___

---

## H. Keselamatan & peranan — AC-017, AC-021

### H1. Permission matrix (AC-017)
- **Steps:** Log masuk setiap peranan URS; bandingkan menu dengan [PANDUAN_LOGIN.md](PANDUAN_LOGIN.md).
- **Expected:** Tiada menu legacy; Pegawai Teknikal tiada aliran aktif; Peraku vs PEPU ikut matriks.
- **Pass/Fail:** ___  **Remarks:** ___

### H2. Skop data (AC-021)
- **Steps:** ALP hanya data sendiri; Admin/JP lihat semua; cuba capaian silang.
- **Expected:** 403 untuk silang ALP. **Residual:** skop khas JKEW belum lengkap.
- **Pass/Fail:** ___  **Remarks:** ___

### H3. Manual dalam sistem (M11 / UR-012)
- **Steps:** Buka `/manual`.
- **Expected:** Panduan dalam aplikasi dipaparkan. **Residual:** PDF rasmi pemilik proses.
- **Pass/Fail:** ___  **Remarks:** ___

### H4. Pengguna tidak aktif / wajib tukar kata laluan
- **Steps:** Nyahaktifkan pengguna; reset kata laluan admin.
- **Expected:** Log masuk disekat; dipaksa tukar kata laluan selepas reset.
- **Pass/Fail:** ___  **Remarks:** ___

---

## I. Integriti & regresi teknikal (bukan AC URS Word, wajib sebelum keluar UAT)

| Semakan | Cara | Status |
|---------|------|--------|
| Suite PHPUnit aktif | `php vendor/bin/phpunit --exclude-group legacy` | 170 OK (5 Sep 2026) |
| Integriti kewangan | `php artisan integrity:check` | 0 pengecualian |
| Route legacy | `route:list` tiada `projects.*` / `expenses.*` / `budget-requests.*` aktif | OK (Fasa 6) |
| Sidebar | Tiada pautan projek/belanja/refund/maker-checker | OK |

---

## Residual diketahui (jangan anggap Fail UAT jika di luar skop semasa)

1. SEC-007 — skop data JKEW penuh  
2. UI TBL-10 F–L polish penuh  
3. PDF Manual M11 rasmi dari pemilik proses  
4. Entiti persatuan berasingan (CRS) — kini medan ROS pada permohonan  
5. Checklist item semakan JP berstruktur (UR-M04-001)  

---

## Keputusan UAT (isi selepas sesi)

| Medan | Nilai |
|-------|-------|
| Tarikh sesi | ___ |
| Persekitaran | ___ |
| Bil. Pass / Fail / Blocked | ___ / ___ / ___ |
| Go / No-Go | ___ |
| Ditandatangani | ___ |
