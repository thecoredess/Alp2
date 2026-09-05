# URS v1.2 — System Inventory (Fasa 2)

**Tarikh:** 5 September 2026  
**Status:** Discovery lengkap — **tiada perubahan kod aplikasi aktif**  
**SSOT:** `URS/URS_Sistem_Pengurusan_Sumbangan ALP DBKL_v1_2_5926.docx` (v1.2)  
**Rujukan lanjut:** [URS_v1.2_GAP_ANALYSIS.md](URS_v1.2_GAP_ANALYSIS.md) · [URS_TRACEABILITY.md](URS_TRACEABILITY.md) · [URS_v1.2_REFACTORING_PLAN.md](URS_v1.2_REFACTORING_PLAN.md)

---

## 1. Ringkasan skim sistem semasa

Sistem Laravel 12 ALP DBKL dibina sebagai **platform bajet/projek korporat + lapisan sumbangan URS (Hybrid)**. URS v1.2 hanya meminta **sistem pengurusan sumbangan JP** (permohonan → semakan → perakuan → kelulusan → baucar/JKEW → laporan aktiviti).

| Lapisan | Ada dalam sistem? | Dalam URS v1.2? |
|---------|-------------------|-----------------|
| Auth, RBAC, audit, FY, ALP master | Ya | Ya / sokongan |
| Permohonan + semakan + kelulusan + baucar | Ya | Ya (perlu disejajarkan) |
| Notifikasi, laporan, tetapan had | Ya | Ya |
| Ledger + komitmen pada kelulusan | Ya | Sokongan baki (teknikal) |
| Projek + belanja + refund + penutupan | Ya | **Tidak** |
| Cadangan bajet maker-checker | Ya | **Tidak** (peruntukan perlu wujud secara ringkas) |
| Semakan Teknikal + Dashboard Eksekutif korporat | Ya | **Tidak** (dashboard Admin JP berbeza) |

---

## 2. Inventory routes (domain)

| Domain | Route utama | Nota |
|--------|-------------|------|
| Auth | `login`, `logout`, password reset/change | Aktif |
| Dashboard | `dashboard`, `dashboard.executive`, `dashboard.finance` | Executive/finance = skop semakan |
| Design | `design.reference` | Bukan URS |
| Laporan | `reports.*` (allocation, ledger, reconciliation, maker-checker, data-quality, applications, projects, csr, audit) | Sebahagian di luar URS |
| Bajet | `budget.mine`, `allocations.*`, `budget-requests.*`, `budget-approvals.*` | Maker-checker di luar URS |
| Permohonan | `applications.*`, wizard, items, documents | Teras URS |
| Semakan | `reviews.secretariat`, `.finance`, `.technical` | Teknikal di luar URS |
| Kelulusan | `approvals.*`, `approval-matrix.*` | Teras URS |
| Notifikasi | `notifications.*` | Teras URS |
| Pembayaran | `payments.*` | Teras URS (perlu ditambah JKEW) |
| Projek | `projects.*`, milestones, report | **Di luar URS** |
| Belanja / Refund | `expenses.*`, `refunds.*`, project-documents | **Di luar URS** |
| Master | `financial-years.*`, `alps.*`, `users.*` | Sokongan URS |
| Tetapan | `settings.*` (Polisi URS) | Teras URS |

Tiada `routes/api.php`. Tiada scheduled task aktif.

---

## 3. Inventory controllers (29)

| Controller | Domain |
|------------|--------|
| Auth/* (4) | Login, reset, tukar kata laluan |
| DashboardController | Dashboard ×3 |
| Application* / Review / Approval* / Payment | Aliran sumbangan |
| Allocation / BudgetRequest / BudgetApproval | Bajet korporat |
| Project* / ProjectExpense / ProjectRefund / ProjectDocument / ProjectMilestone / ProjectReport | Modul projek |
| ReportController | Laporan |
| FinancialYear / Alp / User / SystemSetting / Notification | Master & tetapan |

---

## 4. Inventory models (30) & jadual DB

**Teras sumbangan / sokongan:** `User`, `Alp`, `FinancialYear`, `Allocation`, `BudgetTransaction`, `Application` (+ items, documents, reviews, approvals, histories, revisions), `ApprovalLevel`, `DocumentRequirement`, `SystemSetting`, `AuditLog`, notifications (Laravel)

**Modul projek (calon legacy):** `Project`, `ProjectMilestone`, `ProjectExpense`, `ProjectExpenseRefund`, `ProjectDocument`, `ProjectReport`, + histories

**Governance bajet (calon legacy / simplify):** `BudgetRequest`, `BudgetRequestHistory`

---

## 5. Inventory menu (sidebar)

| Menu | Route | Calon klasifikasi |
|------|-------|-------------------|
| Dashboard | `dashboard` | UBAH → Dashboard Admin JP / ALP |
| Dashboard Eksekutif | `dashboard.executive` | KELUARKAN / ganti M02 |
| Dashboard Kewangan | `dashboard.finance` | KELUARKAN / sebahagian ke M06 |
| Laporan | `reports.index` | UBAH (buang maker-checker/projek jika legacy) |
| Ahli Lembaga | `alps.index` | KEKAL |
| Bajet Saya | `budget.mine` | KEKAL (baki sumbangan) |
| Peruntukan (Ringkasan) | `allocations.index` | KEKAL / simplify |
| Cadangan Bajet (Maker) | `budget-requests.index` | KELUARKAN → reference |
| Kelulusan Bajet (Checker) | `budget-approvals.queue` | KELUARKAN → reference |
| Permohonan Saya / Semua | `applications.*` | KEKAL / UBAH |
| Semakan Urus Setia | `reviews.secretariat` | UBAH → Pegawai JP |
| Semakan Kewangan | `reviews.finance` | SEMAKAN (bukan aliran URS pra-kelulusan) |
| Semakan Teknikal | `reviews.technical` | KELUARKAN → reference |
| Kelulusan | `approvals.queue` | UBAH → Peraku + PEPU |
| Projek Saya / Semua | `projects.*` | KELUARKAN → reference |
| Pengesahan Perbelanjaan | `expenses.queue` | KELUARKAN → reference |
| Pembayaran / Baucar | `payments.index` | KEKAL / UBAH (+ JKEW) |
| Pengesahan Refund | `refunds.queue` | KELUARKAN → reference |
| Pengguna / FY / Matriks / Polisi URS | admin | KEKAL / UBAH |
| Notifikasi | `notifications.index` | KEKAL |
| Templat Reka Bentuk | `design.reference` | KELUARKAN → reference |

---

## 6. Roles & permissions semasa

| Role sistem | URS v1.2 padanan |
|-------------|------------------|
| `alp` | ALP |
| `pegawai_urussetia` | ≈ Pegawai JP |
| `urussetia_alp` | Tiada padanan jelas (bantu ALP) — SEMAKAN |
| `pegawai_kewangan` | ≈ Kerani Kewangan JP + sebahagian JKEW (bercampur) |
| `pegawai_teknikal` | Tiada dalam URS — KELUARKAN / nyahaktif |
| `pelulus` | ≈ TP/Pengarah (Peraku) dan/atau PEPU (bercampur) |
| `pengurusan` | ≈ PEPU / Admin paparan |
| `system_admin` | ≈ Pentadbir Sistem / sebahagian Admin JP |
| `super_admin` | Teknikal (Gate::before) — KEKAL teknikal |

---

## 7. Notifikasi semasa

| Event | URS NT |
|-------|--------|
| submitted | NT-001 (separa — sasaran) |
| revision_required | NT-002 |
| approved / rejected | NT-005 |
| payment_voucher / payment_paid | NT-006 |
| *(tiada)* menunggu perakuan khusus | NT-003 |
| *(tiada)* menunggu PEPU khusus | NT-004 |
| *(tiada)* peringatan report card | NT-007 |

---

## 8. Ujian Feature (37 kelas)

Kumpulan teras URS: Application*, Approval*, Review*, Urs*, Payment*, Auth*, User*, Alp*, FinancialYear*, Reporting* (applications/audit)

Kumpulan legacy/projek: Project*, Expense*, Refund*, BudgetGovernance*, BudgetDecrease*, DataQuality* (projek), ReportingFinancial* (ledger projek)

---

## 9. Integrasi & scheduler

- Tiada API luaran / ERP / JPKKB  
- Tiada cron/schedule  
- Fail: storan peribadi Laravel  
- Eksport: XLSX/PDF dalam ReportController; CSV payments  

---

**Seterusnya:** Gap Analysis & klasifikasi A–E dalam `docs/URS_v1.2_GAP_ANALYSIS.md`.
