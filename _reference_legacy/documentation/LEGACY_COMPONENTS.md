# LEGACY COMPONENTS — URS v1.2 Realignment

**Lokasi folder:** `/_reference_legacy/`  
**Tarikh senarai:** 5 September 2026  
**Status:** **SENARAI RENCANA** — fail kod **belum** dipindahkan (menunggu kelulusan Fasa 5–6).

> Komponen di bawah **tidak lagi menjadi sebahagian daripada skop sistem aktif** berdasarkan URS terkini (v1.2).  
> Ia akan diasingkan sebagai **arkib rujukan pembangunan** sahaja: tiada route, menu, include, atau dependency dari aplikasi production.

---

## Senarai komponen untuk dikeluarkan

| Komponen | Lokasi Asal (aktif) | Sebab Dikeluarkan | URS Terkini | Lokasi Reference (sasaran) |
| -------- | ------------------- | ----------------- | ----------- | -------------------------- |
| Modul Projek (controller) | `app/Http/Controllers/Project*.php`, Milestone, Report, Document | Tiada kitaran projek dalam URS | Tiada M-projek | `_reference_legacy/backend/controllers/project/` |
| Modul Belanja | `ProjectExpenseController` + services verify | Belanja ≠ baucar URS | M06 = baucar/JKEW sahaja | `_reference_legacy/backend/controllers/expense/` |
| Modul Refund | `ProjectRefundController` + services | Tiada dalam URS | Tiada | `_reference_legacy/backend/controllers/refund/` |
| Project services | `app/Services/Project/**` | Sokongan modul projek | Tiada | `_reference_legacy/backend/services/project/` |
| Project models | `app/Models/Project*.php` | Domain projek | Tiada | `_reference_legacy/backend/models/project/` |
| Views projek/belanja/refund | `resources/views/projects/**`, `expenses/**`, `refunds/**` | UI luar skop | Tiada | `_reference_legacy/pages/projects/` dll. |
| Cadangan Bajet Maker | `BudgetRequestController` | Governance korporat | Tiada; M10 = had siling | `_reference_legacy/backend/controllers/budget-request/` |
| Kelulusan Bajet Checker | `BudgetApprovalController` | Sama | Tiada | `_reference_legacy/backend/controllers/budget-approval/` |
| Budget request services/models | `BudgetRequestService`, `BudgetRequestApprovalService`, models | Sama | Tiada | `_reference_legacy/backend/` |
| Semakan Teknikal | `reviews.technical`, ReviewType TECHNICAL | Aliran URS: Pegawai JP sahaja pra-peraku | M04 | `_reference_legacy/modules/technical-review/` |
| Dashboard Eksekutif | `dashboard.executive` | Bukan M02 Admin JP | M02 | `_reference_legacy/pages/dashboard-executive/` |
| Dashboard Kewangan (ops) | `dashboard.finance` (giliran belanja) | Ops projek | Tiada | `_reference_legacy/pages/dashboard-finance/` |
| Laporan projek/CSR/maker-checker/rekonsiliasi | `ReportController` actions + views | Di luar M08 sumbangan | M08 | `_reference_legacy/modules/reports-corporate/` |
| Design reference | `design.reference` | Scaffold UI | Tiada | `_reference_legacy/pages/design-reference/` |
| ProjectCreation pada kelulusan | `ProjectCreationService` dipanggil ApprovalService | Dependency legacy | Tiada | Dokumentasikan putusan dalam backend |
| Tests projek/belanja/refund/budget gov | `tests/Feature/Project*`, `*Expense*`, `*Refund*`, `BudgetGovernance*` dll. | Ujian domain legacy | — | `_reference_legacy/backend/tests/` (salinan) |
| Dokumentasi Hybrid sebagai skop rasmi | `docs/URS_ADDENDUM_HYBRID_v1.1.md` dll. | Bukan SSOT lagi | Diganti v1.2 | `_reference_legacy/documentation/hybrid-era/` (salinan rujukan) |

---

## Database — JANGAN DROP automatik

Lihat cadangan penuh dalam `docs/URS_v1.2_DATABASE_CLEANUP_PROPOSAL.md`.

Ringkas: jadual `projects*`, `project_expenses*`, `project_expense_refunds*`, `budget_requests*` → **ARCHIVE** (kekal di DB sehingga kelulusan; tiada kod aktif menulis).

---

## Peraturan folder reference

Fail dalam `/_reference_legacy/` **TIDAK** boleh:

- didaftarkan dalam `routes/web.php`;
- dipaut dari sidebar/menu;
- di-`require` / `use` oleh kod aktif;
- dipanggil AJAX/frontend;
- dijadualkan cron.

Ia hanyalah arkib untuk rujukan pembangunan.

---

## Log pemindahan (diisi semasa Fasa 6)

| Tarikh | Komponen | Dari | Ke | Dilakukan oleh |
|--------|----------|------|-----|----------------|
| 2026-09-05 | Project* controllers + services (salinan) | `app/Http/Controllers/Project*`, `app/Services/Project` | `_reference_legacy/backend/` | Fasa 6 |
| 2026-09-05 | Budget request controllers/services (salinan) | BudgetRequest* | `_reference_legacy/backend/` | Fasa 6 |
| 2026-09-05 | Views projek/belanja/refund/bajet (salinan) | `resources/views/*` | `_reference_legacy/pages/` | Fasa 6 |
| 2026-09-05 | Docs Hybrid (salinan) | `docs/*HYBRID*` | `_reference_legacy/documentation/hybrid-era/` | Fasa 6 |
| 2026-09-05 | Feature tests legacy | `tests/Feature/Project*` dll. | `tests/Legacy/` + salinan reference | Fasa 6 |
| 2026-09-05 | Routes legacy dinyahaktif | `routes/web.php` | tiada route aktif | Fasa 6 |
| 2026-09-05 | Auto ProjectCreation putus | `ApprovalService` | komitmen tanpa projek | Fasa 6 |
| 2026-09-05 | Aliran semakan | JP → terus kelulusan | kewangan/teknikal pra-lulus dinyahaktif | Fasa 6 |

> **Nota:** Kelas PHP legacy masih wujud dalam `app/` untuk elak autoload pecah / data historikal, tetapi **tiada route/menu** memanggilnya. Gelombang seterusnya boleh memadam fail `app/` selepas tempoh penahanan.
