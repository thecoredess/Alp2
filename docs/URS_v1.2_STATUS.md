# Status URS v1.2 — Sedia UAT Manusia

**Tarikh:** 5 September 2026  
**SSOT:** URS v1.2  
**Automasi:** PHPUnit Feature+Unit **183 OK** (exclude legacy) · termasuk `UrsSpineSmokeTest` (spine E2E)

---

## Apa yang sudah siap (kod)

| Area | Status |
|------|--------|
| Fasa 6 Legacy isolation | DONE — tiada route projek/belanja/maker-checker aktif |
| Fasa 7 Fit URS (7.1–7.12) | DONE |
| Fasa 8–10 Validate / Trace / Audit | DONE |
| SEC-007 JKEW | Role `pegawai_jkew` · `jkew@dbkl.test` |
| UR-M04-001 Checklist JP | DONE |
| NT-003/004 Notifikasi Peraku/PEPU | DONE |
| TBL-10 F–L + M10 templat | DONE |
| M11 Manual | HTML + muat naik PDF rasmi |
| Entiti penerima | `recipients` + UI `/penerima` |
| Smoke E2E spine | `tests/Feature/UrsSpineSmokeTest.php` |

---

## Cara mula UAT (3 langkah)

1. Buka http://localhost/alp/public/login — pilih akaun dari dropdown (kata laluan `password`)  
2. Pastikan **Polisi URS ON** di `/tetapan/polisi-urs`  
3. Ikut senario dalam [UAT_CHECKLIST.md](UAT_CHECKLIST.md) (AC-001…021)

Akaun pantas: [PANDUAN_LOGIN.md](PANDUAN_LOGIN.md)

---

## Tindakan pemilik proses (bukan kod)

1. Muat naik **PDF Manual rasmi** di `/manual` (Admin)  
2. Sahkan kandungan surat/borang (templat M10 boleh disunting)  
3. Lengkapkan sesi UAT → tandatangan Go/No-Go dalam checklist  
4. Jangan gunakan dokumen Hybrid sebagai SSOT ([PANDUAN_ANALISIS_URS_HYBRID.md](PANDUAN_ANALISIS_URS_HYBRID.md) diganti)

---

## Rujukan

- [PANDUAN_ANALISIS_URS_v1.2.md](PANDUAN_ANALISIS_URS_v1.2.md)  
- [URS_TRACEABILITY.md](URS_TRACEABILITY.md)  
- [URS_v1.2_FINAL_AUDIT.md](URS_v1.2_FINAL_AUDIT.md)  
- [URS_v1.2_REFACTORING_PLAN.md](URS_v1.2_REFACTORING_PLAN.md)  
