# Role & Permission Matrix — Sistem ALP DBKL

Sumber kebenaran: `App\Support\Permissions::forRoles()` (di-seed oleh `RolePermissionSeeder`).
Jadual ini dijana daripada pemetaan **sebenar** dalam pangkalan data (bukan andaian).
Kebenaran dikuatkuasa di **backend** (Policy/Gate/`can`), bukan sekadar menyembunyikan butang UI.

> Dijana pada Fasa 7 (Hardening). Kemas kini jika `Permissions::forRoles()` berubah.

## Prinsip teras (invarian)

| Invarian | Status |
|---|---|
| **System Admin ≠ Finance Operator** — System Admin boleh *lihat* bajet tetapi TIADA `allocations.request.*`, `adjustments.request.*`, `*.approve`, `expenses.*`, `refunds.*`, `dashboard.finance`, `reports.financial` | ✅ Dikuatkuasa |
| **ALP = rekod sendiri sahaja** — tiada `*.view_all`; skop `alp_id` sendiri dikuatkuasa di controller/policy | ✅ Dikuatkuasa |
| **Maker ≠ Checker** — dikuatkuasa oleh identiti (`approver/verifier != created_by && != submitted_by`), termasuk untuk Super Admin (diaudit `SUPER_ADMIN_FINANCIAL_OVERRIDE`) | ✅ Dikuatkuasa |
| **Super Admin** — melepasi semua semakan melalui `Gate::before`; tetapi maker-checker kewangan tetap terpakai | ✅ Dikuatkuasa |

## Peranan (9)

`super_admin`, `system_admin`, `alp`, `urussetia_alp`, `pegawai_urussetia` (Urus Setia DBKL),
`pegawai_kewangan` (Finance Maker), `pegawai_teknikal` (Technical Officer), `pelulus` (Approver / Finance Checker),
`pengurusan` (Management).

## Matriks fungsi × peranan

Legenda: ✅ = ada kebenaran penuh · 👁 = lihat sahaja · — = tiada · (own) = skop sendiri

| Fungsi | Super Admin | System Admin | ALP | Urus Setia ALP | Urus Setia DBKL | Finance (Kewangan) | Technical | Approver (Pelulus) | Management |
|---|---|---|---|---|---|---|---|---|---|
| Dashboard asas | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Dashboard Kewangan | ✅ | — | — | — | — | ✅ | — | ✅ | ✅ |
| Dashboard Eksekutif | ✅ | — | — | — | — | — | — | — | ✅ |
| Urus Pengguna | ✅ | ✅ | — | — | — | — | — | — | — |
| Urus ALP | ✅ | ✅ | — | 👁 | 👁 | 👁 | 👁 | 👁 | 👁 |
| Tahun Kewangan (urus) | ✅ | ✅ | — | — | — | — | — | — | 👁 |
| Peruntukan (lihat) | ✅ | 👁 | (own) | (own) | 👁 | 👁 | — | 👁 | 👁 |
| Cadangan bajet — MAKER | ✅ | — | — | — | — | ✅ | — | — | — |
| Kelulusan bajet — CHECKER | ✅ | — | — | — | — | — | — | ✅ | — |
| Cipta permohonan | ✅ | — | ✅ | ✅ | — | — | — | — | — |
| Lihat semua permohonan | ✅ | 👁 | (own) | (own) | ✅ | ✅ | ✅ | ✅ | ✅ |
| Semakan Urus Setia | ✅ | — | — | — | ✅ | — | — | — | — |
| Semakan Kewangan | ✅ | — | — | — | — | ✅ | — | — | — |
| Semakan Teknikal | ✅ | — | — | — | — | — | ✅ | — | — |
| Kelulusan permohonan | ✅ | — | — | — | — | — | — | ✅ | ✅ |
| Matriks kelulusan (urus) | ✅ | ✅ | — | — | — | — | — | — | 👁 |
| Projek (lihat) | ✅ | 👁 | (own) | (own) | ✅ | 👁 | 👁 | 👁 | 👁 |
| Projek (kemajuan/milestone/complete) | ✅ | — | — | — | ✅ | — | — | — | — |
| Perbelanjaan — MAKER | ✅ | — | — | — | — | ✅ | — | — | — |
| Perbelanjaan — CHECKER (verify) | ✅ | — | — | — | — | — | — | ✅ | — |
| Dokumen bukti perbelanjaan (urus) | ✅ | 👁 | — | — | 👁 | ✅ | 👁 | 👁 | 👁 |
| Refund — MAKER | ✅ | — | — | — | — | ✅ | — | — | — |
| Refund — CHECKER (verify) | ✅ | — | — | — | — | — | — | ✅ | — |
| Dokumen penutupan (urus) | ✅ | 👁 | — | — | ✅ | 👁 | 👁 | 👁 | 👁 |
| Tutup projek | ✅ | — | — | — | — | — | — | ✅ | — |
| Laporan Kewangan | ✅ | — | — | — | — | ✅ | — | ✅ | ✅ |
| Laporan Permohonan | ✅ | ✅ | — | — | ✅ | — | — | — | ✅ |
| Laporan Projek | ✅ | ✅ | — | — | ✅ | ✅ | ✅ | ✅ | ✅ |
| Laporan CSR | ✅ | — | — | — | ✅ | — | — | — | ✅ |
| Laporan Audit | ✅ | ✅ | — | — | — | — | — | — | ✅ |
| Eksport (XLSX/PDF) | ✅ | ✅ | (own) | (own) | ✅ | ✅ | ✅ | ✅ | ✅ |
| Tetapan sistem | ✅ | ✅ | — | — | — | — | — | — | — |

> ALP/Urus Setia ALP mempunyai `reports.view` + `reports.export` **skop-sendiri sahaja** — mereka tiada
> kebenaran laporan kewangan/projek/CSR/audit terperinci, jadi mengakses URL laporan tersebut → **403**.

## Kebenaran penuh mengikut peranan (dari DB — Fasa 7)

**SUPER_ADMIN** — (tiada senarai eksplisit; `Gate::before` meluluskan semua; maker-checker kewangan tetap terpakai).

**SYSTEM_ADMIN** — `allocations.view, alps.create, alps.deactivate, alps.update, alps.view, applications.view_all, approval_matrix.manage, approval_matrix.view, budget.view_all, dashboard.view, expenses.documents.view, financial_years.create, financial_years.manage, financial_years.update, financial_years.view, projects.closure-documents.view, projects.view_all, refunds.view, reports.applications, reports.audit, reports.export, reports.projects, reports.view, roles.view, settings.manage, users.assign_role, users.create, users.deactivate, users.update, users.view`
→ **Tiada** kebenaran kewangan operasi (create/adjust/approve/verify).

**ALP** — `applications.create, dashboard.view, projects.view, reports.export, reports.view`

**URUSSETIA_ALP** — `alps.view, applications.create, dashboard.view, projects.view, reports.export, reports.view`

**PEGAWAI_URUSSETIA** (Urus Setia DBKL / operator projek) — `allocations.view, alps.view, applications.review.secretariat, applications.view_all, dashboard.view, expenses.documents.view, expenses.view, project-reports.create, projects.closure-documents.manage, projects.closure-documents.view, projects.complete, projects.milestones, projects.progress, projects.update, projects.view_all, refunds.view, reports.applications, reports.csr, reports.export, reports.projects, reports.view`

**PEGAWAI_KEWANGAN** (Finance Maker) — `adjustments.request.create/submit/update, allocations.request.create/submit/update, allocations.view, alps.view, applications.review.finance, applications.view_all, budget.view_all, dashboard.finance, dashboard.view, expenses.create/submit/update/view, expenses.documents.manage/view, projects.closure-documents.view, projects.view_all, refunds.create/submit/update/view, reports.export, reports.financial, reports.projects, reports.view`
→ **Maker sahaja** — tiada `*.approve` / `*.verify`.

**PEGAWAI_TEKNIKAL** — `alps.view, applications.review.technical, applications.view_all, dashboard.view, expenses.documents.view, projects.closure-documents.view, projects.view_all, refunds.view, reports.projects, reports.view`

**PELULUS** (Approver / Finance Checker) — `adjustments.approve/reject/return, allocations.approve/reject/return, allocations.view, alps.view, applications.approve, applications.reject, applications.view_all, budget.view_all, dashboard.finance, dashboard.view, expenses.documents.view, expenses.reject/return/verify/view, project-reports.review, projects.close, projects.closure-documents.view, projects.view_all, refunds.reject/return/verify/view, reports.export, reports.financial, reports.projects, reports.view`
→ **Checker sahaja** — tiada `*.request.*` / `expenses.create` / `refunds.create`.

**PENGURUSAN** (Management) — `allocations.view, alps.view, applications.approve, applications.reject, applications.view_all, approval_matrix.view, budget.view_all, dashboard.executive, dashboard.finance, dashboard.view, expenses.documents.view, financial_years.view, projects.closure-documents.view, projects.view_all, refunds.view, reports.applications, reports.audit, reports.csr, reports.export, reports.financial, reports.projects, reports.view`
