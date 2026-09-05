# DATABASE CLEANUP PROPOSAL — URS v1.2

**Tarikh:** 5 September 2026  
**Peraturan:** JANGAN `DROP TABLE` / `DROP COLUMN` / padam data pukal sehingga kelulusan bertulis + backup.

---

## Jadual cadangan

| Table / Column | Kegunaan Lama | Digunakan Sistem URS Aktif? | Dependency | Risiko | Cadangan |
| -------------- | ------------- | ---------------------------: | ---------- | ------ | -------- |
| `users`, Spatie tables | Auth/RBAC | Ya | Semua | Tinggi | **Kekalkan** |
| `alps` | Master ALP | Ya | Applications, allocations | Tinggi | **Kekalkan** (+ guna appointment fields) |
| `financial_years` | Tahun | Ya | Allocations, apps | Tinggi | **Kekalkan** |
| `allocations` | Akaun peruntukan | Ya (baki) | Ledger, apps | Tinggi | **Kekalkan** |
| `budget_transactions` | Ledger | Ya (ALLOCATION + COMMITMENT) | Baki | Tinggi | **Kekalkan**; henti tulis EXPENDITURE/REFUND dari aktif |
| `budget_requests` (+ histories) | Maker-checker bajet | Tidak (selepas simplify) | Legacy UI | Sederhana | **Archive** — tiada route aktif; data kekal |
| `applications` (+ related) | Permohonan | Ya | Teras | Tinggi | **Kekalkan**; tambah medan URS kemudian |
| `applications.payment_*` | Baucar | Ya | M06 | Tinggi | **Kekalkan**; tambah medan JKEW |
| `application_reviews` | Semakan | Ya (JP); teknikal legacy | M04 | Sederhana | **Kekalkan**; henti type=technical |
| `approval_levels`, `application_approvals` | Kelulusan | Ya | M05 | Tinggi | **Kekalkan** |
| `document_requirements`, app documents | Dokumen | Ya | M03 | Tinggi | **Kekalkan**; seed semula TBL-9 |
| `system_settings`, `notifications`, `audit_logs` | Tetapan/NT/audit | Ya | M10/NT/M09 | Tinggi | **Kekalkan** |
| `projects` (+ sequences, milestones, histories) | Modul projek | Tidak | Approval create | Tinggi jika drop awal | **Archive** |
| `project_expenses*` | Belanja | Tidak | Ledger EXPENDITURE | Tinggi | **Archive** |
| `project_expense_refunds*` | Refund | Tidak | Ledger REFUND | Tinggi | **Archive** |
| `project_documents`, requirements | Bukti projek | Tidak | — | Sederhana | **Archive** |
| `project_reports` | Laporan projek | Tidak (ganti report card app) | — | Sederhana | **Archive** |
| `application_id` → `projects` | 1 app = 1 project | Tidak selepas putus | ApprovalService | Sederhana | Henti create; FK kekal nullable/historikal |

---

## Transaksi ledger — polisi selepas realignment

| Type | Aktif URS? | Nota |
|------|------------|------|
| INITIAL_ALLOCATION / ALLOCATION_ADJUSTMENT | Ya (cara ringkas Admin) | Ganti maker-checker |
| COMMITMENT / COMMITMENT_REVERSAL | Ya | Pada lulus/tolak permohonan |
| EXPENDITURE / REFUND / COMMITMENT_RELEASE / PROJECT_CLOSURE_* | Tidak (aktif) | Legacy historikal sahaja |

---

## Langkah selamat (bila diluluskan)

1. Backup penuh `mysqldump` + `storage/app/private`  
2. Kod aktif tidak lagi rujuk jadual legacy  
3. Tandakan jadual dalam dokumentasi ARCHIVE  
4. (Pilihan kemudian) migrate `*_archived` rename atau dump berasingan  
5. DROP hanya selepas tempoh penahanan data DBKL  
