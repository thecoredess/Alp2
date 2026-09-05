# PHASE 7 — HARDENING / UAT READINESS REPORT

**Sistem Ahli Lembaga Penasihat (ALP) DBKL**
Tarikh: 2026-08-20 · Skop: Pengukuhan keselamatan, integriti & kesediaan UAT (LOCAL ONLY)

---

**STATUS:** ✅ COMPLETE — semua gate lulus. Tiada ciri perniagaan baharu, tiada reka bentuk semula aliran kerja, tiada perubahan semantik ledger. Tiada commit / push / deploy.

**SECURITY:** PASS — had kadar ditambah: log masuk `throttle:20,1` (di atas kunci 5-percubaan berasaskan kelayakan sedia ada), reset kata laluan `throttle:6,1`, laporan/eksport `throttle:60,1`. Disahkan CSRF (middleware web + `@csrf`), XSS (Blade `{{ }}`; satu-satunya `{!! !!}` = SVG ikon hardcoded), mass-assignment (`$fillable`; tiada `$request->all()`), middleware pengguna-tidak-aktif & wajib-tukar-kata-laluan, anti-enumerasi reset kata laluan.

**FINANCIAL INTEGRITY:** PASS — arahan baharu `php artisan integrity:check` (LAPOR SAHAJA) menggabungkan semakan hala-depan (sumber→ledger) dengan hala-balik (ledger→sumber: COMMITMENT/EXPENDITURE/REFUND tanpa sumber diluluskan/disahkan), duplikasi projek/peruntukan, dan pelanggaran identiti maker==checker. Hasil sihat: **`Financial Integrity Exceptions = 0`** (kod keluar 0).

**MAKER-CHECKER:** PASS — peraturan identiti (`approver/verifier != created_by && != submitted_by`) disahkan untuk allocation, adjustment, expense, refund; dikuatkuasa walaupun pengguna ada kebenaran luas (ujian regresi hijau).

**CONCURRENCY:** PASS — ujian idempotensi/atomicity/konkurensi sedia ada (penghantaran serentak, kelulusan akhir, kelulusan peruntukan, pengesahan perbelanjaan/refund, penutupan-vs-pengesahan, duplikasi projek) kekal hijau; kekangan unik + kunci baris utuh.

**FILE SECURITY:** PASS — disk peribadi, muat turun berkuasa (stream), nama fail rawak, tiada path traversal (laluan dari DB bukan input pengguna), had MIME + sambungan + 10 MB, SHA-256, immutable selepas VERIFIED/CLOSED. Muat turun tanpa kebenaran → 403.

**EXPORT SECURITY:** PASS — XLSX menneutralkan suntikan formula (teks bermula `= + - @`/tab/CR/LF diawali apostrof; dibuktikan `ExportSecurityTest`); wang kekal numerik tepat (bukan float); URL eksport perlukan `reports.export`; nama fail selamat `<slug>-YYYYMMDD`; kunci sensitif audit ditapis.

**AUTHORIZATION:** PASS — ALP → 403 pada dashboard eksekutif/kewangan, laporan kewangan/audit, dan URL eksport langsung (halaman BM 403 baharu dipaparkan dalam pelayar); capaian penuh pengurusan disahkan.

**PERFORMANCE:** PASS (disemak) — dashboard/laporan guna agregasi + eager loading; binaan data Dashboard Eksekutif diukur = 42 pertanyaan untuk 4 projek (serta-merta). Penunjuk integriti `O(projek)` & Ledger tanpa paginasi didokumentasikan sebagai pertimbangan skala (cache/cron/paginasi) dalam `DEPLOYMENT_READINESS.md §7b` — **bukan masalah UAT**, jadi tidak di-refactor mengikut peraturan fasa.

**ERROR HANDLING:** PASS — halaman BM tersuai untuk 403/404/419/422/429/500/503 (kandungan sendiri, tiada stack trace/laluan/rahsia); penindasan production bergantung `APP_DEBUG=false` (didokumen).

**ACCESSIBILITY / UI:** PASS (smoke) — label pada medan borang, mesej validasi BM, status melalui teks badge (bukan warna sahaja), jadual lebar dalam `overflow-x-auto`, mobil 375px + desktop disahkan fasa terdahulu; tiada defek ketara.

**ROLE MATRIX:** COMPLETE — `docs/ROLE_PERMISSION_MATRIX.md` dijana daripada pemetaan **sebenar** dalam DB; mengesahkan System Admin ≠ Finance Operator, ALP = rekod sendiri sahaja, Maker ≠ Checker.

**UAT CHECKLIST:** COMPLETE — `docs/UAT_CHECKLIST.md` (senario ikut peranan: Precondition/Steps/Expected/Pass-Fail/Remarks).

**SECURITY CHECKLIST:** COMPLETE — `docs/SECURITY_CHECKLIST.md` (VERIFIED / CONFIGURED / ACTION, ditanda jujur).

**DEPLOYMENT READINESS:** COMPLETE — `docs/DEPLOYMENT_READINESS.md` (env, sambungan termasuk bcmath/zip, migrasi + backup-dahulu, amaran seed production, backup/restore/retensi, rollback).

**BACKUP / RESTORE:** Didokumen (mysqldump + arkib `storage/app/private`, ujian restore, retensi) — tiada backup awan dikonfigur (mengikut arahan).

**TEST RESULTS:** **223 lulus, 474 assertion** (220 + 3 ujian export-security baharu). Tiada inflasi.

**MYSQL 8.4:** PASS — suite penuh pada `alp_dbkl_test`; `integrity:check` pada `alp_dbkl` = 0.

**FRONTEND BUILD:** PASS — `npm run build` bersih.

**BROWSER SMOKE:** PASS — log masuk, dashboard (eksekutif/kewangan), laporan (peruntukan/ledger/audit), eksport XLSX + PDF (content-type betul), dan halaman BM **403** baharu — semua disahkan pada port 8010.

**FILES CREATED:**
- `app/Console/Commands/IntegrityCheck.php`
- `resources/views/errors/{layout,403,404,419,422,429,500,503}.blade.php`
- `docs/{ROLE_PERMISSION_MATRIX,UAT_CHECKLIST,UAT_ISSUE_TEMPLATE,SECURITY_CHECKLIST,DEPLOYMENT_READINESS}.md`
- `tests/Feature/ExportSecurityTest.php`

**FILES MODIFIED:**
- `routes/web.php` (had kadar login/reset/laporan)
- `app/Support/Export/XlsxWriter.php` (guard formula-injection)
- `README.md` (sync Fasa 7 — nama enum disahkan tepat; subfasa 4A/5A hadir)

**KNOWN LIMITATIONS:** Penunjuk integriti dashboard `O(projek)`; laporan Ledger tanpa paginasi (baki berjalan perlukan jujukan penuh) — cache/cron/paginasi disyorkan sebelum production skala besar (didokumen). Item production (HTTPS, `APP_DEBUG=false`, kebenaran storage, backup) kekal ⚠ ACTION.

**UAT READINESS VERDICT:** ✅ **UAT READY** (bukan *production ready* — deployment production belum disahkan).

**GIT STATUS:** Tracked-modified fasa ini: `README.md`, `routes/web.php` (+ perubahan Fasa 6 belum di-commit). Untracked baharu: `docs/`, `resources/views/errors/`, `app/Console/Commands/IntegrityCheck.php`, `tests/Feature/ExportSecurityTest.php`. Tiada commit/push/deploy.

**RECOMMENDED NEXT STEP:** Jalankan kitaran UAT dengan penguji sebenar guna `docs/UAT_CHECKLIST.md`, log isu melalui `docs/UAT_ISSUE_TEMPLATE.md`; tangani Critical/High sebelum fasa production-readiness (HTTPS, `APP_DEBUG=false`, backup, dan pengoptimuman skala yang didokumen).
