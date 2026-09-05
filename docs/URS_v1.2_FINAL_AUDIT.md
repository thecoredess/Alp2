# Final Audit — URS v1.2 Realignment (Fasa 10)

**Tarikh:** 5 September 2026  
**SSOT:** URS v1.2 sahaja (Hybrid **bukan** skop rasmi)  
**Rujukan:** [URS_v1.2_REFACTORING_PLAN.md](URS_v1.2_REFACTORING_PLAN.md) · [URS_TRACEABILITY.md](URS_TRACEABILITY.md) · [UAT_CHECKLIST.md](UAT_CHECKLIST.md)

---

## 1. Checklist teknikal

| Item | Hasil | Bukti |
|------|-------|-------|
| PHPUnit Feature + Unit (bukan Legacy) | **PASS** 170 tests / 408 assertions | `phpunit --exclude-group legacy` |
| Legacy suite diasingkan | Ya (`tests/Legacy/`, `@group legacy`) | Tidak dalam `phpunit.xml` suites |
| `integrity:check` | **0** pengecualian | Artisan |
| Route aktif projek/belanja/refund/cadangan-bajet | **Tiada** | `route:list --name=projects\|expenses\|budget-request` → empty |
| Sidebar legacy links | **Tiada** | `resources/views/partials/sidebar.blade.php` |
| Approval → cipta projek | **Diputuskan** | COMMITMENT sahaja (Fasa 6) |
| DROP TABLE legacy | **Tidak dilakukan** (dasar) | DB archive kekal |
| Hybrid sebagai SSOT docs aktif | **Tidak** | Docs merujuk v1.2 |

---

## 2. Spine operasi aktif (URS)

```
Login → ALP / FY → Admin set peruntukan
  → Wizard permohonan (TBL-10 + TBL-9)
  → Semakan Pegawai JP → Kelulusan Peraku/PEPU
  → COMMITMENT → Pembayaran / JKEW fields
  → Report card → Laporan / Audit / Manual / Notifikasi
```

---

## 3. Residual diterima (bukan blocker Fasa 10 kod)

| ID | Jurang | Impak UAT |
|----|--------|-----------|
| SEC-007 | Skop data JKEW | PARTIAL — role `pegawai_jkew` + `payments.jkew_scope`; akaun `jkew@dbkl.test` |
| UR-M04-001 | Checklist item semakan berstruktur | COMPLETE — 6 item; RECOMMEND wajib semua lengkap |
| M10 templat | Templat surat/borang boleh sunting | PARTIAL — kepala/badan/kaki di Polisi URS; laporan penuh CRS |
| M11 PDF | Manual PDF rasmi pemilik proses | PARTIAL — Admin muat naik di `/manual`; cetak ringkasan HTML |
| CRS persatuan | Entiti penerima berasingan | PARTIAL — `recipients` + UI `/penerima` |
| TBL-10 F–L | Polish medan lanjutan UI | PARTIAL — F–J ledger + K–L keputusan dipaparkan |
| Spatie role values | Label URS; slug lama (`pelulus`, dll.) | AC-017 Separa |
| NT-003 / NT-004 | Notifikasi Peraku / PEPU | PARTIAL — dihantar ikut matriks |

---

## 4. Larangan kekal

- Jangan reintroduce Hybrid sebagai requirement production  
- Jangan restore maker-checker / projek / belanja ke menu aktif  
- Jangan DROP jadual legacy tanpa keputusan data retention bertulis  

---

## 5. Keputusan Fasa 10

| Medan | Status |
|-------|--------|
| Realignment kod Fasa 6–8 | **Selesai** |
| Traceability Fasa 9 | **Dikemas** (lihat `URS_TRACEABILITY.md`) |
| Sedia sesi UAT manusia | **Ya** — ikut `UAT_CHECKLIST.md` |
| Go-live production | **Tertakluk** keputusan UAT + residual di atas |

**Ditandatangani (teknikal):** Auto / refactor URS v1.2 — 5 Sep 2026  
**Ditandatangani (pemilik proses):** ________________

**Ringkasan operasi semasa:** [URS_v1.2_STATUS.md](URS_v1.2_STATUS.md)
