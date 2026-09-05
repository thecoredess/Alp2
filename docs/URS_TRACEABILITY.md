# URS Traceability Matrix — v1.2

**SSOT:** URS Sistem Pengurusan Sumbangan ALP DBKL v1.2  
**Tarikh:** 5 September 2026  
**Status legend:** COMPLETE · PARTIAL · NOT IMPLEMENTED · NEED REVIEW · LEGACY (akan dikeluarkan)

Format: `URS → Modul → Page/Route → Backend → Database`

---

## M01 — Log Masuk & Pengguna

| URS | Requirement | Modul | Page / Route | Backend | Database | Status |
|-----|-------------|-------|--------------|---------|----------|--------|
| M01 / UR-M01 | Login e-mel | Auth | `/login` | `AuthenticatedSessionController` | `users`, `sessions` | COMPLETE |
| M01 | Urus akaun/peranan | Users | `/pengguna` | `UserController` | `users`, Spatie | PARTIAL (label URS; value Spatie lama) |
| BR-007 / UR-M01-004 | Kelayakan ikut lantikan | ALP + Bajet | allocate | `UrsContributionPolicy::maxAnnualForAlp` | `alps.appointment_*` | PARTIAL |
| SEC-001…009 | RBAC + nyahaktif | AuthZ | — | Policies / Gates | Spatie | PARTIAL |

---

## M02 — Dashboard Pemantauan

| URS | Requirement | Modul | Page / Route | Backend | Database | Status |
|-----|-------------|-------|--------------|---------|----------|--------|
| UR-M02-001…012 | Dashboard Admin JP + penapis | Dashboard | `/dashboard` | `DashboardController` | apps, allocations | PARTIAL |
| UR-M02-003…005 | Timeline hingga JKEW + KPI 14 hari | Dashboard + app | `/dashboard`, tab Timeline | `ApplicationTimelineService` | histories + `sent_to_jkew_at` | PARTIAL |
| AC-011 / AC-020 | Tertunggak + statistik | Dashboard | `/dashboard` | overdue logic | `applications` | PARTIAL (ambang lalai 14 hari) |
| — | Dashboard Eksekutif | — | `/dashboard/eksekutif` | `executive()` | — | LEGACY |
| — | Dashboard Kewangan ops | — | `/dashboard/kewangan` | `finance()` | — | LEGACY |

---

## M03 — Permohonan Sumbangan

| URS | Requirement | Modul | Page / Route | Backend | Database | Status |
|-----|-------------|-------|--------------|---------|----------|--------|
| UR-M03 | Wizard / draf / hantar | Permohonan | `/permohonan/*` | `ApplicationWizardController`, `ApplicationSubmissionService` | `applications`, items, docs | PARTIAL |
| TBL-10 A–P | Medan borang penyaluran | Permohonan | wizard + `<x-urs-tbl10-summary>` + borang | `Tbl10BudgetSnapshot` | `applications.*` + ledger | PARTIAL (F–J/K–L dipaparkan; tandatangan basah manual) |
| TBL-9 | Dokumen ROS/EFT/bank/kertas | Permohonan | wizard dokumen | `DocumentRequirement` | `document_requirements` | PARTIAL (set wajib URS) |
| BR-001…005 | Had wang & tempoh | Polisi | submit + settings | `UrsContributionPolicy` | `system_settings` | PARTIAL (lalai ON) |
| BR-007 | Kelayakan ikut lantikan | Bajet | allocate | `maxAnnualForAlp` | `alps.appointment_*` | PARTIAL (proration linear) |
| BR-008/009/023 | 1 persatuan; sekali/tahun | Permohonan | submit + wizard | `RecipientRegistry` + `ApplicationSubmissionService` | `recipients` + `applications.recipient_*` | PARTIAL (entiti ROS; unik/tahun) |
| BR-010…015 | KL, jenis, 2 bulan, BR-012 | Permohonan | wizard + submit | polisi + deklarasi | `is_short_notice`, `compliance_*` | PARTIAL |
| BR-020 | Cetak Borang Penyaluran | Permohonan | `/permohonan/{id}/borang-penyaluran` | `ApplicationController@borang` | — | PARTIAL |

---

## M04 — Semakan JP

| URS | Requirement | Modul | Page / Route | Backend | Database | Status |
|-----|-------------|-------|--------------|---------|----------|--------|
| UR-M04 | Semakan kelengkapan + baki | Semakan | `/semakan/urus-setia` | `ReviewController`, `ApplicationReviewService` | `application_reviews` | PARTIAL |
| UR-M04-001 | Checklist item lengkap | Semakan | `reviews/show` | `JpReviewChecklist` + JSON `checklist` | `application_reviews.checklist` | COMPLETE |
| — | Semakan Kewangan pra-lulus | Semakan | — | ReviewType::FINANCE | — | LEGACY (route dinyahaktif) |
| — | Semakan Teknikal | Semakan | — | ReviewType::TECHNICAL | — | LEGACY |

---

## M05 — Kelulusan (Peraku + PEPU)

| URS | Requirement | Modul | Page / Route | Backend | Database | Status |
|-----|-------------|-------|--------------|---------|----------|--------|
| UR-M05-001 | Peraku TP/Pengarah | Kelulusan | `/kelulusan` | `ApprovalController`, `ApprovalService` | `application_approvals`, `approval_levels` | PARTIAL (≤RM3k) |
| UR-M05-002 | PEPU lulus/tolak | Kelulusan | sama | matriks aras 2 | sama | PARTIAL (>RM3k → pengurusan) |
| BR-020 | Surat status kelulusan | Kelulusan | `/permohonan/{id}/surat-kelulusan` | `ApplicationController@letter` | — | PARTIAL |
| — | Auto-cipta Projek | Projek | — | `ProjectCreationService` | `projects` | LEGACY (diputuskan) |

---

## M06 — Pengurusan Pembayaran

| URS | Requirement | Modul | Page / Route | Backend | Database | Status |
|-----|-------------|-------|--------------|---------|----------|--------|
| UR-M06-001 | Rekod baucar | Pembayaran | `/pembayaran` | `PaymentController`, `ApplicationPaymentService` | `applications.payment_*` | PARTIAL |
| UR-M06 | Tarikh hantar JKEW | Pembayaran | form bayaran | `sent_to_jkew_at` | `applications.sent_to_jkew_at` | PARTIAL |
| BR-013 / AC-012 | Semakan silang JPKKB | Pembayaran | form bayaran | `jkew_crosscheck_*` | `applications.jkew_*` | PARTIAL (rekod manual) |
| SEC-007 | Skop data JKEW | Pembayaran | `/pembayaran` | `payments.jkew_scope` (+ tanpa manage) | `applications.sent_to_jkew*` | PARTIAL (permission; role JKEW khusus CRS) |
| NT-006 | Notifikasi bayaran | Notifikasi | — | `ApplicationNotifier` | `notifications` | COMPLETE |

---

## M07 — Laporan Program / Report Card

| URS | Requirement | Modul | Page / Route | Backend | Database | Status |
|-----|-------------|-------|--------------|---------|----------|--------|
| UR-M07 / BR-018/019 | Report card aktiviti | Program | tab Report Card + `/laporan-aktiviti` | `ApplicationReportCardService` | `report_card_*`, documents | PARTIAL |
| NT-007 | Peringatan 1 bulan | Notifikasi | `urs:remind-report-cards` | `ApplicationNotifier::reportCardReminder` | `notifications` | PARTIAL (jadual harian) |

---

## M08 — Laporan & Analisis

| URS | Requirement | Modul | Page / Route | Backend | Database | Status |
|-----|-------------|-------|--------------|---------|----------|--------|
| UR-M08 | Laporan ikut ALP/bulan/status/baki | Laporan | `/laporan/permohonan` dll. | `ApplicationReportService`, `FinancialReportService` | ledger + apps | PARTIAL |
| — | Laporan projek / CSR projek / maker-checker | Laporan | `/laporan/projek`, `/csr`, maker-checker | Project*/MakerChecker* | projects | LEGACY |

---

## M09 — Jejak Audit

| URS | Requirement | Modul | Page / Route | Backend | Database | Status |
|-----|-------------|-------|--------------|---------|----------|--------|
| UR-M09 / SEC-004 | Rekod tindakan utama | Audit | `/laporan/audit` | `AuditService`, `AuditReportService` | `audit_logs` | COMPLETE |

---

## M10 — Tetapan Sistem

| URS | Requirement | Modul | Page / Route | Backend | Database | Status |
|-----|-------------|-------|--------------|---------|----------|--------|
| BR-006 / M10 | Had siling dinamik | Tetapan | `/tetapan/polisi-urs` | `SystemSettingController`, `UrsContributionPolicy` | `system_settings` | PARTIAL |
| UR-M10-002 | Templat surat / borang | Tetapan | `/tetapan/polisi-urs` | `UrsDocumentTemplates` | `system_settings` | PARTIAL (teks kepala/badan/kaki) |
| M10 | Templat laporan penuh / borang daftar | — | — | — | — | NOT IMPLEMENTED |
| Matriks kelulusan | Had kuasa | Pentadbiran | `/pentadbiran/matriks-kelulusan` | `ApprovalMatrixController` | `approval_levels` | PARTIAL (sokongan M05) |

---

## M11 — Manual / Kit Tatacara

| URS | Requirement | Modul | Page / Route | Backend | Database | Status |
|-----|-------------|-------|--------------|---------|----------|--------|
| UR-M11 / UR-012 | Manual ikut peranan | Manual | `/manual` + PDF | `ManualController`, `OfficialManual` | `system_settings` + storage | PARTIAL (HTML + muat naik PDF rasmi) |

---

## Notifikasi (Bah. 10)

| URS | Requirement | Status |
|-----|-------------|--------|
| NT-001 Hantar | PARTIAL |
| NT-002 Pindaan | COMPLETE |
| NT-003 Menunggu perakuan | PARTIAL (`awaiting_peraku` → role matriks aras 1) |
| NT-004 Menunggu PEPU | PARTIAL (`awaiting_pepu` → role matriks aras seterusnya) |
| NT-005 Keputusan | COMPLETE |
| NT-006 Bayaran | COMPLETE |
| NT-007 Report card | PARTIAL (`urs:remind-report-cards`) |

---

## Keperluan umum & BR ringkas

| URS | Status |
|-----|--------|
| UR-001…012 | Campuran PARTIAL (lihat panduan v1.2 + UAT) |
| BR-001…005, 021–022 (wang) | PARTIAL (lalai ON; UAT wajib ON) |
| BR-006 | PARTIAL (UI tetapan) |
| BR-007…015, 023 | PARTIAL (Fasa 7.4–7.6; bukan NOT IMPLEMENTED) |
| BR-016…020 | PARTIAL (surat, baucar, borang, report card) |
| AC-001…021 | [UAT_CHECKLIST.md](UAT_CHECKLIST.md) + panduan §10 |

---

## Validasi (Fasa 8)

| Semakan | Status |
|---------|--------|
| PHPUnit Feature+Unit | COMPLETE (170 OK, 5 Sep 2026) |
| `integrity:check` | COMPLETE (0 pengecualian) |
| Menu/route legacy aktif | COMPLETE (tiada) |
| Checklist UAT AC | COMPLETE (dokumen) — sesi manusia tertunggak |

---

## Modul di luar URS (diasingkan — tiada route aktif)

| Fungsi | Route (dulu) | Backend | DB | Status |
|--------|--------------|---------|-----|--------|
| Projek | `/projek*` | `Project*` | `projects*` | LEGACY (kod di `_reference_legacy` / app tanpa route) |
| Belanja | `/perbelanjaan*` | `ProjectExpense*` | `project_expenses*` | LEGACY |
| Refund | `/refund*` | `ProjectRefund*` | `project_expense_refunds*` | LEGACY |
| Cadangan bajet | `/cadangan-bajet*`, `/kelulusan-bajet*` | `BudgetRequest*` | `budget_requests*` | LEGACY |
| Set peruntukan Admin JP | `/peruntukan/cipta`, `/peruntukan/{id}/laras` | `AllocationController` + `BudgetService` | `allocations`, ledger | COMPLETE (Fasa 7.12) |
| Design ref | `/reka-bentuk` | view only | — | LEGACY |

---

**Kemas kini:** Residual kod ditutup secara PARTIAL/COMPLETE: SEC-007, UR-M04-001, NT-003/004, TBL-10 F–L, M10 templat, M11 PDF upload, `recipients`. Tertunggak pemilik: kandungan PDF rasmi final, role JKEW khusus, polish CRS lanjut.  
**Audit akhir:** [URS_v1.2_FINAL_AUDIT.md](URS_v1.2_FINAL_AUDIT.md)
