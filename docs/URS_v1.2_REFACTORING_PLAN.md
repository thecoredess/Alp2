# Pelan Refactoring — Selaras URS v1.2

**Tarikh:** 5 September 2026  
**Status:** **Fasa 6–10 selesai** (5 Sep 2026) — Isolation → Fit → Validate → Trace → Final Audit  
**SSOT:** URS v1.2  
**Dokumen asas:**  
- [URS_v1.2_SYSTEM_INVENTORY.md](URS_v1.2_SYSTEM_INVENTORY.md)  
- [URS_v1.2_GAP_ANALYSIS.md](URS_v1.2_GAP_ANALYSIS.md)  
- [URS_TRACEABILITY.md](URS_TRACEABILITY.md)  
- [_reference_legacy/documentation/LEGACY_COMPONENTS.md](../_reference_legacy/documentation/LEGACY_COMPONENTS.md)  
- [URS_v1.2_DATABASE_CLEANUP_PROPOSAL.md](URS_v1.2_DATABASE_CLEANUP_PROPOSAL.md)

---

## Keputusan yang WAJIB sebelum kod dipindah

| # | Keputusan | Cadangan analisis | Impak jika ditangguh |
|---|-----------|-------------------|----------------------|
| D1 | URS v1.2 = SSOT tunggal (Hybrid dibatalkan sebagai skop rasmi) | **Ya** | Analisis ini diguna |
| D2 | Cara set peruntukan tanpa maker-checker | Admin JP set allocation + ledger entry atomik | Blok Fasa 6 bajet |
| D3 | Buang Semakan Kewangan pra-kelulusan? | **Ya** (Kerani selepas lulus sahaja) | Aliran M04 |
| D4 | Buang Semakan Teknikal? | **Ya** → reference | Menu/role |
| D5 | Data projek historikal | Archive DB, tiada UI aktif | E4 |
| D6 | Polisi URS | Wajib ON (tiada OFF dalam UI ops) | AC wang |
| D7 | ID persatuan (AD-005) | No. ROS | BR-009 |

**Tanpa D1–D7 bertulis, Fasa 6 tidak dimulakan.**

---

## Ringkasan impact (Fasa 5)

### Dependency kritikal

```
ApprovalService.approve()
    └── ProjectCreationService  ← PUTUSKAN (hentikan cipta projek)

BudgetService
    ├── Application submission (baki)     ← KEKAL
    ├── Approval COMMITMENT               ← KEKAL
    └── Expense/Refund EXPENDITURE        ← HENTI dari aktif

Sidebar + Permissions
    └── Banyak gate ke projects/expenses  ← BERSIHKAN

Application.status IN_PROGRESS/COMPLETED/CLOSED
    └── Dipacu projek                     ← SEMAK semula status URS
```

### Risiko

| Risiko | Tahap | Mitigasi |
|--------|-------|----------|
| Putus create project → broken UI projek | Tinggi | Buang route/menu dahulu dalam satu PR |
| Ledger historikal expenditure kekal | Sederhana | Baca sahaja; integrity check mode legacy |
| Ujian merah besar | Tinggi | Quarantine test legacy ke reference; kekalkan ujian URS |
| Role remap pecahkan login UAT | Sederhana | Seed alias + panduan login baharu |
| Kehilangan jejak audit projek | Rendah | Data DB archive kekal |

### Anggaran saiz kerja

| Fasa | Skop | Anggaran (petunjuk) |
|------|------|---------------------|
| 6 Isolation | Pindah kod + putus route/menu/Approval | Besar |
| 7a Remap role + aliran M04/M05 | Sederhana–Besar |
| 7b Borang/dokumen/BR | Besar |
| 7c M06 JKEW + M02 timeline | Sederhana–Besar |
| 7d M07 report card + M11 | Sederhana |
| 8–10 UAT & audit | Sederhana |

---

## Urutan pelaksanaan (selepas kelulusan)

### Fasa 6 — Legacy Isolation (tanpa DROP DB)

1. Backup DB + kod branch khusus `urs-v12-realign`.  
2. Putuskan `ProjectCreationService` dari `ApprovalService`.  
3. Buang/disable route + sidebar: projek, belanja, refund, cadangan bajet, kelulusan bajet, semakan teknikal, dashboard eksekutif/kewangan ops, design ref, laporan korporat.  
4. Salin kod berkaitan ke `_reference_legacy/` (mengikut LEGACY_COMPONENTS.md).  
5. Grep seluruh repo: tiada `use`/`route` aktif ke kelas yang dipindah.  
6. Pastikan login + permohonan + semakan JP + kelulusan + pembayaran masih boot.  
7. Kemas `LEGACY_COMPONENTS.md` log pemindahan.

### Fasa 7 — Refactor aktif (bergilir PR kecil)

| Wave | Isi | Status |
|------|-----|--------|
| 7.1 | Role/permission TBL-04 + panduan login | **DONE** (label URS; Spatie value kekal) |
| 7.2 | Aliran M04 (JP sahaja) + M05 (Peraku→PEPU) | **DONE** (matriks 2 aras; UI queue/letter) |
| 7.3 | Polisi URS wajib + BR ID v1.2 dalam kod | **DONE** (lalai ON; UI settings BR-001/002/003/005) |
| 7.4 | Model penerima + BR-008/009/023 | **DONE** (medan + validasi hantar) |
| 7.5 | Medan TBL-10 + dokumen TBL-9 | **DONE** (komponen TBL-10; wizard; TBL-9 seeder) |
| 7.6 | BR-007, 010…015 | **DONE** (proration lantikan; KL; short notice; BR-012 deklarasi) |
| 7.7 | M06 JKEW fields + skop JKEW | **DONE** (`sent_to_jkew_at`, crosscheck, status SENT_TO_JKEW) |
| 7.8 | M02 timeline + KPI 14 hari | **DONE** (tab Timeline; dashboard KPI watchlist) |
| 7.9 | M07 report card pada application + NT-007 | **DONE** (upload, senarai JP, `urs:remind-report-cards`) |
| 7.10 | M11 manual PDF | **DONE** (halaman `/manual` dalam sistem; PDF rasmi masih milik proses) |
| 7.11 | Cetak Borang Penyaluran (BR-020) | **DONE** (`/permohonan/{id}/borang-penyaluran`) |
| 7.12 | Simplify set peruntukan Admin (ganti maker-checker) | **DONE** (`allocations.manage`; `/peruntukan/cipta` + laras) |

### Arahan seterusnya

**Fasa 6–10 selesai.** Kerja kod realignment ditutup. Seterusnya: **sesi UAT manusia** ikut [UAT_CHECKLIST.md](UAT_CHECKLIST.md); keputusan go-live oleh pemilik proses.

### Fasa 8 — Validation — **DONE** (5 Sep 2026)

| Semakan | Hasil |
|---------|-------|
| PHPUnit Feature+Unit (`--exclude-group legacy`) | **170 OK**, 408 assertions |
| `integrity:check` | **0** pengecualian |
| Menu/sidebar aktif | Tiada pautan projek / belanja / refund / maker-checker |
| Route legacy aktif | Tiada `projects.*` / `expenses.*` / `budget-requests.*` dalam `web.php` |
| UAT AC-001…021 | [UAT_CHECKLIST.md](UAT_CHECKLIST.md) selaras URS v1.2 |
| Fix ujian | `ApplicationInfoRequest` authorize policy; payload TBL-10; kiraan projek → `tests/Legacy` |

### Fasa 9 — Traceability — **DONE** (5 Sep 2026)

- `URS_TRACEABILITY.md` dikemas: BR-007…023 = PARTIAL; blok Validasi Fasa 8; residual jelas  

### Fasa 10 — Final Audit — **DONE** (5 Sep 2026)

- [URS_v1.2_FINAL_AUDIT.md](URS_v1.2_FINAL_AUDIT.md)  
- Checklist: tiada route/menu legacy aktif; SSOT = URS v1.2; residual disenaraikan untuk CRS/UAT  

---

## Apa yang TIDAK dibuat sekarang

- ❌ Padam fail aplikasi  
- ❌ DROP TABLE  
- ❌ Implement feature baharu di luar senarai F  
- ❌ “Hybrid dual-mode” sebagai requirement  

## CADANGAN — BUKAN REQUIREMENT URS

Item berikut **tidak** boleh masuk production tanpa CRS/URS:

- App mudah alih asli  
- Integrasi ERP/GIRO automatik  
- Self-register ALP awam  
- Kecuali dinyatakan semula dalam pindaan URS  

---

## Keadaan akhir struktur (sasaran)

```text
alp/
├── app/ …                 ← Sistem aktif (URS v1.2 sahaja + teknikal B)
├── resources/views/ …
├── routes/web.php         ← Tiada route legacy
├── docs/                  ← Traceability + gap (aktif)
└── _reference_legacy/     ← Arkib kod lama (tiada dependency)
    ├── modules/
    ├── pages/
    ├── backend/
    ├── database/
    ├── assets/
    └── documentation/
        └── LEGACY_COMPONENTS.md
```

---

## Arahan seterusnya kepada pasukan

1. Pemilik proses **sahkan D1–D7**.  
2. Selepas sah, berikan arahan: **“Mula Fasa 6 Legacy Isolation”**.  
3. Jangan campur Fasa 6 dengan pembangunan BR baharu dalam PR yang sama.
