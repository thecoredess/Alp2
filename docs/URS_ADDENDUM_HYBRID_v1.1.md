# Addendum URS — Model Hybrid Sistem ALP DBKL

> **DIGANTI / BUKAN SSOT (5 Sep 2026):** Addendum Hybrid **bukan** skop rasmi production.  
> **SSOT = URS v1.2.** Rujuk [PANDUAN_ANALISIS_URS_v1.2.md](PANDUAN_ANALISIS_URS_v1.2.md). Fail ini dikekalkan untuk jejak sejarah keputusan sahaja.

**Dokumen:** Addendum kepada *URS Sistem Pengurusan Permohonan & Peruntukan Sumbangan ALP DBKL* (Draf Standard v1.0, 25 Ogos 2026)  
**Panduan analisis penuh:** [PANDUAN_ANALISIS_URS_HYBRID.md](PANDUAN_ANALISIS_URS_HYBRID.md)  
**Versi addendum:** 1.1  
**Tarikh:** 26 Ogos 2026  
**Status:** ~~Cadangan untuk pengesahan~~ → **Diganti oleh realignment URS v1.2**  
**Rujukan sistem:** Laravel ALP DBKL (UAT Ready + Fasa A–C hybrid)

---

## 1. Tujuan

Addendum ini merekodkan **keputusan arah Hybrid** yang telah dilaksanakan dalam sistem, supaya URS v1.0 tidak bercanggah dengan platform bajet/projek sedia ada.

| Arah | Keterangan |
|------|------------|
| **Dipilih** | Kekalkan platform korporat (ledger, maker-checker, projek, belanja, refund). Tambah **lapisan polisi sumbangan URS** yang boleh diaktifkan. |
| **Tidak dipilih** | Buang modul projek/ledger untuk jadi sistem sumbangan kecil sahaja. |

Pemilik proses diminta mengesahkan seksyen **Bahagian 6**, **Bahagian 16 (P01–P17)**, dan jadual pemetaan modul sebelum URS rasmi dikemas kini ke v1.1.

---

## 2. Model dwi-aliran (wajib dinyatakan dalam URS)

Sistem mengendalikan **dua aliran kewangan yang berbeza**:

### 2.1 Aliran A — Sumbangan ALP (URS)

1. ALP hantar permohonan (wizard + dokumen).  
2. Semakan (Urus Setia / Kewangan / Teknikal — lihat §4).  
3. Peraku / Pelulus (matriks kelulusan).  
4. Kelulusan → **COMMITMENT** ledger + projek dijana.  
5. Status **pembayaran/baucar** dikemas kini (Menunggu → Baucar Disedia → Dibayar).  
6. Polisi URS (jika ON): BR-001…004 dikuatkuasakan.

### 2.2 Aliran B — Belanja projek (korporat)

1. Pegawai Kewangan rekod perbelanjaan terhadap projek.  
2. Pelulus sahkan → **EXPENDITURE** ledger.  
3. Refund / penutupan projek mengikut peraturan maker-checker.

**Penting:** Status baucar (Aliran A) **tidak** mencipta transaksi `EXPENDITURE`. Belanja projek (Aliran B) kekal berasingan. Kedua-dua aliran mesti digambarkan dalam URS Bah. proses & Bah. data.

---

## 3. Cadangan teks Bahagian 6 — Perkara Luar Skop

*(Bahagian 6 dalam URS v1.0 draf kosong. Cadangan teks di bawah.)*

Sistem **tidak** mencakupi perkara berikut pada skop semasa:

| No. | Perkara luar skop | Nota |
|-----|-------------------|------|
| OS-01 | Integrasi langsung dengan sistem kewangan DBKL / e-baucar berpusat / ERP | Status baucar direkod secara manual dalam sistem ALP |
| OS-02 | Pemindahan dana elektronik automatik (GIRO/FPX) kepada akaun ALP | Diurus luar sistem; hanya status & rujukan direkod |
| OS-03 | Pendaftaran kendiri (self-register) ALP awam tanpa kelulusan admin | Akaun dicipta oleh pentadbir (lihat P03) |
| OS-04 | Aplikasi mudah alih asli (iOS/Android) | Web responsif sahaja |
| OS-05 | Portal awam tanpa log masuk untuk semak status | Memerlukan autentikasi |
| OS-06 | Pengurusan inventori / aset fizikal program | Di luar domain peruntukan |
| OS-07 | Modul penggajian / elaun ALP selain sumbangan program | — |
| OS-08 | Penukaran mata wang asing | RM sahaja |
| OS-09 | Tandatangan digital / e-meterai sah di sisi undang-undang | Surat kelulusan boleh dicetak; e-sign luar skop |
| OS-10 | Arkib eDokumen DBKL / rekod pengurusan fail kertas | Muat naik dokumen sokongan dalam sistem sahaja |

Sebarang item di atas yang mahu dimasukkan skop memerlukan **CRS / URS tambahan**.

---

## 4. Kemas kini modul URS (v1.0 → Hybrid)

| Modul URS | Status selepas Hybrid | Perubahan teks URS yang disyorkan |
|-----------|----------------------|-----------------------------------|
| M1 Login & Pengguna | Separa → **Diterima** | Akaun dicipta admin; tiada self-register (P03) |
| M2 Dashboard | Dilengkapkan | Termasuk notifikasi, tertunggak, kuota tempoh (jika polisi ON), giliran Peraku/Pelulus & pembayaran |
| M3 Permohonan | Dilengkapkan | Had RM3k / kuota tempoh dikuatkuasa **hanya jika Polisi URS aktif** |
| M4 Semakan JP | **Diperluas** | URS asal: 1 peringkat JP. Sistem: Urus Setia + Kewangan + Teknikal. Nyatakan sebagai skop rasmi |
| M5 Peraku + Pelulus | Dilengkapkan | Label Peraku/Pelulus; matriks berbilang aras; surat kelulusan boleh dicetak |
| M6 Pembayaran | Dilengkapkan | Status baucar pada permohonan diluluskan + CSV; **bukan** integrasi bank |
| M7 Laporan program | Kekal | Melalui modul projek / laporan akhir |
| M8 Laporan & analisis | Kekal | XLSX/PDF; CSV untuk pembayaran |
| M9 Jejak audit | Kekal | Termasuk audit kemaskini pembayaran |
| M10 Tetapan | Dilengkapkan | Skrin **Polisi URS**: had tahunan, had permohonan, kuota tempoh, ambang tertunggak |
| Notifikasi | Dilengkapkan | In-app + e-mel: hantar, kembalikan, lulus, tolak, baucar, dibayar |

---

## 5. Peraturan perniagaan (BR) — interpretasi Hybrid

| ID | Peraturan URS | Interpretasi sistem |
|----|---------------|---------------------|
| BR-001 | Maks RM30,000 / ALP / tahun | Dikuatkuasa pada kelulusan peruntukan awal **jika Polisi URS ON** (tetapan boleh diubah) |
| BR-002 | Maks RM3,000 / permohonan | Dikuatkuasa pada hantar/hantar semula **jika ON** |
| BR-003 | 3 tempoh × RM10,000 | Tempoh kalendar: Jan–Apr, Mei–Ogos, Sep–Dis; kuota boleh dikonfigurasi |
| BR-004 | Baki tempoh luput | Tiada bawa ke hadapan; penggunaan dikira ikut `submitted_at` dalam tetingkap tempoh |
| BR-005 | Tiada had bilangan permohonan | Kekal |
| BR-006 | Peruntukan individu, tidak kongsi | Kekal (`alp_id`) |
| BR-007 | Melebihi baki ditolak | Kekal (Ledger Available − Pending) |

**Klausa baharu yang perlu dimasukkan URS:**

> Polisi sumbangan BR-001…004 adalah **pilihan konfigurasi**. Apabila dimatikan, sistem membenarkan aliran projek/ledger tanpa had RM30k/RM3k/kuota tempoh (untuk program CSR/pembangunan berskala lebih besar).

---

## 6. Pemetaan peranan

| Peranan URS | RoleName sistem | Nota |
|-------------|-----------------|------|
| ALP (Pemohon) | `alp` | — |
| Pegawai JP / Urus Setia | `pegawai_urussetia` / `urussetia_alp` | Semakan peringkat awal |
| Pengarah JP (Peraku) | `pelulus` (aras matriks 1) | Label UI: Peraku |
| Pengarah Eksekutif (Pelulus) | `pelulus` / `pengurusan` | Mengikut matriks jumlah |
| JKEW / Kerani bayaran | `pegawai_kewangan` | `payments.manage` untuk status baucar |
| Pentadbir JPM | `system_admin` / `super_admin` | — |
| *(lebihan)* | `pegawai_teknikal`, `pengurusan` | Semakan teknikal & dashboard eksekutif — **skop rasmi hybrid** |

---

## 7. Bahagian 16 — Keputusan cadangan P01–P17

Item berikut dalam URS v1.0 dinyatakan “untuk pengesahan pemilik proses”. Jadual ini mencadangkan keputusan berdasarkan sistem as-built. **Sila tandakan S/T (Setuju/Tolak) sebelum URS rasmi.**

| ID | Isu | Cadangan keputusan | S/T |
|----|-----|--------------------|-----|
| P01 | Skop rasmi: sumbangan kecil vs platform projek | **Hybrid** — kedua-dua wujud; polisi URS pilihan | ☐ |
| P02 | Semakan Kewangan & Teknikal tambahan | **Dalam skop** — nyatakan dalam M4 | ☐ |
| P03 | Pendaftaran kendiri ALP | **Luar skop** (OS-03); akaun oleh admin | ☐ |
| P04 | Had kuasa pelulus | Mengikut **matriks kelulusan** boleh dikonfigurasi (bukan hardcode URS) | ☐ |
| P05 | Medan wajib borang permohonan | Kekalkan set semasa wizard (maklumat, objektif, bajet, dokumen) | ☐ |
| P06 | Dokumen wajib | Mengikut `document_requirements` mengikut jenis permohonan | ☐ |
| P07 | Notifikasi e-mel mandatori | **Ya** — saluran DB + mail (sync); alamat SMTP production perlu dikonfigur | ☐ |
| P08 | SLA / tertunggak | Ambang hari boleh dikonfigurasi (lalai 7 hari) | ☐ |
| P09 | Format surat kelulusan | Template sistem semasa; penyesuaian letterhead DBKL kemudian | ☐ |
| P10 | Integrasi baucar kewangan DBKL | **Luar skop** (OS-01); rekod manual no. baucar | ☐ |
| P11 | CSV eksport pembayaran | **Dalam skop** — disediakan | ☐ |
| P12 | Polisi URS lalai ON atau OFF | **OFF** semasa UAT/projek besar; ON untuk operasi sumbangan ketat | ☐ |
| P13 | Tahun kewangan vs tahun kalendar tempoh | Tempoh 4-bulan guna **tahun kewangan.year** sebagai tahun kalendar | ☐ |
| P14 | Hubungan baucar vs belanja projek | **Berasingan** — baucar ≠ EXPENDITURE | ☐ |
| P15 | Label Peraku dalam RBAC | Label/matriks sahaja; tiada role baharu `peraku` | ☐ |
| P16 | Aplikasi mudah alih | **Luar skop** (OS-04); web responsif | ☐ |
| P17 | Perubahan BR jumlah selepas go-live | Melalui skrin Tetapan Polisi URS + audit; CRS jika formula berubah | ☐ |

---

## 8. Keperluan umum UR-001…010 — status selepas Hybrid

| ID | Keperluan | Status |
|----|-----------|--------|
| UR-001 | Permohonan dalam talian | Dipenuhi |
| UR-002 | Pengguna berasaskan peranan | Dipenuhi |
| UR-003 | Maklumat + dokumen sokongan | Dipenuhi |
| UR-004 | Semakan, pembetulan, perakuan, kelulusan | Dipenuhi (diperluas semakan) |
| UR-005 | Semak baki peruntukan | Dipenuhi |
| UR-006 | Dashboard & laporan | Dipenuhi |
| UR-007 | Notifikasi tindakan/status | Dipenuhi |
| UR-008 | Rekod tindakan (audit) | Dipenuhi |
| UR-009 | Status pembayaran direkod/pantau | Dipenuhi (metadata baucar) |
| UR-010 | Web + mesra mudah alih | Separa — web responsif; app asli luar skop |

---

## 9. Rujukan teknikal (bukan sebahagian URS rasmi)

| Ciri | Lokasi ringkas |
|------|----------------|
| Polisi URS | `app/Support/UrsContributionPolicy.php` · `/tetapan/polisi-urs` |
| Notifikasi | `ApplicationNotifier` · `/notifikasi` |
| Surat kelulusan | `/permohonan/{id}/surat-kelulusan` |
| Pembayaran/baucar | `/pembayaran` · medan `payment_*` pada `applications` |
| Ujian | `UrsPolicyAndNotificationTest`, `UrsPeriodAndOverdueTest`, `ApplicationPaymentTest` |

---

## 10. Tindakan seterusnya (pemilik proses)

1. Semak & tandatangan jadual **P01–P17** (§7).  
2. ~~Masukkan teks **Bahagian 6** (§3) ke dalam fail URS Word rasmi.~~ **Selesai** — `URS/URS_..._Standard_v1.1.docx` (jana semula: `php scripts/build_urs_v1_1.php`).  
3. Kemas kini Bah. modul & BR mengikut §4–§5 *(semak manual dalam Word; rujuk `docs/URS_PANDUAN_KEMASKINI_v1.1.md`)*.  
4. Terbitkan **URS v1.1** rasmi selepas pengesahan pemilik proses.  
5. Jika P03/P10/P16 ditolak (mahu dalam skop), buka tiket pembangunan baharu.

---

**Disediakan untuk:** JPM / pemilik proses ALP DBKL  
**Disediakan oleh:** Pasukan pelaksanaan sistem (Addendum Hybrid)  
**Tanpa tandatangan digital** — salinan kerja untuk semakan
