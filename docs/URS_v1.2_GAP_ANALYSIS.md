# URS v1.2 — Gap Analysis & Klasifikasi (Fasa 3–4)

**Tarikh:** 5 September 2026  
**Prinsip:** URS v1.2 = Single Source of Truth. Kod lama / Hybrid / URS lama **bukan** alasan kekalkan fungsi.  
**Kod aplikasi:** Belum diubah (analisis sahaja).

Klasifikasi:

| Kod | Maksud |
|-----|--------|
| **A** | KEKAL — dinyatakan dalam URS |
| **B** | KEKAL TEKNIKAL — perlu untuk URS berfungsi selamat |
| **C** | UBAH — wujud tetapi tidak selaras URS |
| **D** | KELUARKAN → `_reference_legacy/` — tiada requirement URS |
| **E** | PERLU SEMAKAN — jangan padam sehingga keputusan pemilik |
| **F** | BANGUNKAN — tiada / tidak cukup dalam sistem |

---

## Jadual Gap Analysis utama

| No. | Modul / Fungsi | Sistem Semasa | Rujukan URS | Status | Tindakan |
| --- | -------------- | ------------- | ----------- | ------ | -------- |
| 1 | Login / session / kata laluan | Ada | M01, SEC | **A/B** | Kekalkan |
| 2 | Pengurusan pengguna + RBAC | Ada (9 role) | M01, TBL-04 | **C** | Map ke role URS (Admin JP, Kerani, Pegawai JP, Peraku, PEPU, JKEW) |
| 3 | Master ALP + tempoh lantikan | Ada medan; tidak dikuatkuasa BR-007 | M01, BR-007, AD-002 | **C** | Kuatkuasa kelayakan ikut lantikan |
| 4 | Tahun kewangan | Ada | Sokongan BR tempoh | **B** | Kekalkan (simplify UI jika perlu) |
| 5 | Dashboard umum | Ada | M02 | **C** | Jadikan Dashboard Pemantauan (timeline, KPI 14 hari, penapis) |
| 6 | Dashboard Eksekutif | Ada | Tiada (bukan M02 Admin JP) | **D** | Keluarkan ke reference / ganti paparan M02 |
| 7 | Dashboard Kewangan (ops giliran belanja) | Ada | Tiada sebagai dashboard belanja projek | **D** | Keluarkan; baucar kekal di M06 |
| 8 | Bajet Saya / baki peruntukan | Ada (ledger) | M02/M03, BR-001…003 | **A/B** | Kekalkan pengiraan baki; UI istilah “sumbangan” |
| 9 | Ringkasan peruntukan | Ada | Baki / Admin JP | **A/B** | Kekalkan baca; simplify |
| 10 | Cadangan Bajet Maker-Checker | Ada penuh | Tiada dalam URS | **D** | Reference; ganti dengan tetapan peruntukan Admin (ringkas) — lihat E#1 |
| 11 | Kelulusan Bajet Checker | Ada | Tiada | **D** | Reference (bersama #10) |
| 12 | Ledger `budget_transactions` | Ada | Sokongan baki + komitmen | **B** | Kekalkan jenis yang perlu (allocation + commitment); jenis expenditure/refund → legacy bersama projek |
| 13 | Permohonan wizard | Ada | M03, TBL-10 | **C** | Selaras medan A–P + jenis program URS |
| 14 | Dokumen permohonan | Ada (set lain) | M03, TBL-9 | **C** | Ganti checklist: ROS, EFT, penyata bank, kertas kerja |
| 15 | BR wang (30k/tempoh/3k) | Polisi ON/OFF | BR-001…005, AC-003/004 | **C** | Paksa ON untuk operasi URS; buang dual-mode sebagai lalai production |
| 16 | BR persatuan sekali/tahun | Tiada model | BR-008/009/023 | **F** | Bangunkan entiti penerima + unik tahunan |
| 17 | BR program (KL, jenis, 2 bulan) | Tiada | BR-010…015 | **F** | Validasi + medan |
| 18 | Semakan Pegawai JP (checklist) | Semakan Urus Setia | M04 | **C** | Rename/alir checklist item; hantar ke Peraku |
| 19 | Semakan Kewangan (pra-kelulusan) | Ada | Tiada (Kerani selepas lulus) | **E** | Cadangan: keluarkan dari aliran pra-kelulusan |
| 20 | Semakan Teknikal | Ada | Tiada | **D** | Reference |
| 21 | Peraku + Pelulus (matriks) | Ada | M05 | **C** | Pisah jelas Peraku (TP/Pengarah) vs PEPU; label DISYORKAN |
| 22 | Surat kelulusan | Ada | BR-020, M05 | **C** | Templat Borang/Surat JP |
| 23 | Pembayaran / baucar | Ada metadata | M06 | **C** | Tambah hantar JKEW, silang JPKKB, skop JKEW |
| 24 | Modul Projek (kitaran hayat) | Ada penuh | Tiada | **D** | `_reference_legacy/` |
| 25 | Perbelanjaan projek | Ada | Tiada | **D** | Reference |
| 26 | Refund projek | Ada | Tiada | **D** | Reference |
| 27 | Milestone / laporan akhir projek | Ada | Separa vs M07 report card | **D/C** | Report card baharu di aliran permohonan (bukan projek) |
| 28 | Laporan program / report card ALP | Separa (projek) | M07, BR-018/019 | **F** | Bangunkan pada permohonan diluluskan |
| 29 | Laporan & analisis sumbangan | Ada (campur projek) | M08 | **C** | Tapis laporan URS; buang maker-checker/projek dari menu aktif |
| 30 | Jejak audit | Ada | M09, SEC-004 | **A** | Kekalkan |
| 31 | Tetapan Polisi URS | Ada | M10, BR-006 | **C** | Role Admin JP; templat surat/borang |
| 32 | Manual pengguna M11 | Tiada | M11, UR-012 | **F** | Bangunkan (muat turun PDF minimum) |
| 33 | Notifikasi NT-001…007 | Separa | Bah. 10 | **C/F** | Sempurnakan sasaran peranan + NT-007 |
| 34 | Templat Reka Bentuk UI | Ada | Tiada | **D** | Reference |
| 35 | Integrity check artisan | Ada | Teknikal | **B** | Kekalkan (manual) |
| 36 | Export XLSX/PDF keselamatan | Ada | M08, BR-020 | **A/B** | Kekalkan untuk laporan URS |
| 37 | Role `urussetia_alp` | Ada | Tiada jelas | **E** | Sahkan dengan pemilik |
| 38 | Auto-cipta Projek pada kelulusan | Ada | Tiada | **D** | Putuskan dependency; hentikan create project |

---

## Klasifikasi ringkas mengikut kategori

### A — KEKAL (URS jelas)

- Login, notifikasi inbox asas, audit trail  
- Master ALP (paparan), pengguna (asas)  
- Permohonan (wujudnya modul), semakan JP (wujudnya), kelulusan, baucar (asas)  
- Laporan sumbangan / audit (teras)  
- Paparan baki peruntukan  

### B — KEKAL TEKNIKAL

- Middleware auth/active/password, CSRF, throttle, validation BM  
- Spatie permission engine  
- `Money` / BCMath, ledger allocation + COMMITMENT (untuk baki)  
- Storan fail peribadi, hash dokumen  
- Session, error pages, audit service  
- Financial year sebagai skop tahun  

### C — UBAH

- Role & permission → TBL-04  
- Dashboard → M02 (timeline, 14 hari, penapis Admin JP)  
- Wizard + dokumen → TBL-9/10  
- Polisi URS → wajib ON (atau tiada suis OFF dalam UI production)  
- Aliran semakan → 1 JP (+ hantar perakuan); buang teknikal dari aktif  
- Kelulusan → Peraku kemudian PEPU (jelas)  
- Pembayaran → medan JKEW + silang  
- Laporan menu → buang item projek/maker-checker  
- M10 templat  

### D — KELUARKAN KE `_reference_legacy/`

| Komponen | Sebab |
|----------|-------|
| Modul Projek penuh | Tiada dalam URS v1.2 |
| Perbelanjaan + pengesahan | Aliran B korporat |
| Refund + pengesahan | Aliran B korporat |
| Cadangan/Kelulusan Bajet maker-checker | Governance korporat; bukan M10 |
| Semakan Teknikal | Tiada dalam aliran URS |
| Dashboard Eksekutif / Kewangan (ops belanja) | Bukan M02/M06 URS |
| Halaman design reference | Dev scaffold |
| Laporan: reconciliation projek, maker-checker, CSR projek, ledger belanja | Di luar M08 sumbangan |
| Auto ProjectCreation pada approve | Dependency legacy |

### E — PERLU SEMAKAN (keputusan pemilik sebelum Fasa 6)

| ID | Isu | Soalan |
|----|-----|--------|
| E1 | Cara set peruntukan awal RM30k tanpa maker-checker | Admin JP set terus? Import sekali? |
| E2 | Semakan Kewangan pra-kelulusan | Buang terus atau kekal pilihan? |
| E3 | Role `urussetia_alp` | Kekal sebagai pembantu ALP atau buang? |
| E4 | Data projek/belanja sedia ada dalam DB | Archive sahaja atau papar baca-sahaja sejarah? |
| E5 | `COMMITMENT` ledger selepas lulus tanpa projek | Ya — kekalkan commitment pada Application sahaja |
| E6 | Soft-delete vs hard-hide menu | Wajib keluarkan route aktif (bukan CSS hide) |

### F — BANGUNKAN (jurang URS)

1. Entiti **Penerima/Persatuan** + kawalan sekali/tahun (BR-008/009/023)  
2. Medan borang A–P + jenis program + ROS + akaun bank  
3. Dokumen TBL-9  
4. BR-007 kelayakan ikut tarikh lantikan  
5. BR-010…015 validasi  
6. Checklist semakan M04 item-by-item  
7. Medan M06: tarikh hantar JKEW, hasil silang JPKKB, skop role JKEW  
8. Dashboard timeline + KPI 14 hari (UR-M02, AC-011)  
9. Report card pada permohonan (M07) + NT-007  
10. Modul Manual M11  
11. Role baharu / remap: `admin_jp`, `kerani_kewangan_jp`, `jkew`, label Peraku/PEPU  
12. Cetak Borang Penyaluran Sumbangan (BR-020)  

---

## Pemetaan aliran end-to-end URS vs sistem

| Langkah URS | Sistem semasa | Gap |
|-------------|---------------|-----|
| Login | ✅ | Role names |
| Dashboard pemantauan | ⚠ | Timeline/KPI/penapis |
| Permohonan + dokumen | ⚠ | Medan & checklist |
| Semakan Pegawai JP | ⚠ | + Kewangan + Teknikal (lebihan) |
| Peraku TP/Pengarah | ⚠ | Campur matriks |
| Kelulusan PEPU | ⚠ | Campur matriks |
| Baucar Kerani JP | ⚠ | OK asas |
| Hantar JKEW + silang | ❌ | Bangunkan |
| Status bayaran + NT | ✅ asas | — |
| Report card | ⚠ via projek | Alih ke permohonan |
| Manual | ❌ | Bangunkan |

**Proses tersembunyi lama yang mesti dibuang dari aktif:** cipta projek automatik, belanja, refund, milestone, penutupan projek, cadangan bajet.

---

## Shared components — JANGAN pindah buta

| Komponen | Digunakan oleh URS? | Digunakan oleh Legacy? | Nota |
|----------|---------------------|------------------------|------|
| `BudgetService` / `Money` | Ya (baki, commitment) | Ya (expenditure) | Kekal; sempitkan jenis transaksi aktif |
| `ApprovalService` | Ya | Panggil `ProjectCreationService` | **Putuskan** create project |
| `AuditService` | Ya | Ya | Kekal |
| `Application` model | Ya | Linked `project` | Nullable project_id; henti create |
| `ReportController` | Ya (sebahagian) | Ya | Buang action legacy dari route/menu |
| Spatie roles | Ya | Ya | Remap, jangan padam engine |

---

**Seterusnya:** Impact & pelan fasa dalam `docs/URS_v1.2_REFACTORING_PLAN.md` · Traceability `docs/URS_TRACEABILITY.md` · Legacy list `_reference_legacy/documentation/LEGACY_COMPONENTS.md`.
