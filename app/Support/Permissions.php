<?php

namespace App\Support;

use App\Enums\RoleName;

/**
 * Daftar pusat kebenaran (permissions) sistem ALP DBKL dan pemetaan
 * kepada setiap peranan. Modul akan berkembang mengikut fasa; Fasa 1
 * meliputi pengguna, ALP, tahun kewangan dan dashboard.
 */
final class Permissions
{
    /**
     * Senarai penuh permission mengikut kumpulan modul.
     *
     * @return array<string, list<string>>
     */
    public static function groups(): array
    {
        return [
            'Dashboard' => [
                'dashboard.view',
                'dashboard.executive',
            ],
            'Pengguna' => [
                'users.view',
                'users.create',
                'users.update',
                'users.deactivate',
                'users.assign_role',
            ],
            'Peranan' => [
                'roles.view',
            ],
            'Ahli Lembaga (ALP)' => [
                'alps.view',
                'alps.create',
                'alps.update',
                'alps.deactivate',
            ],
            'Tahun Kewangan' => [
                'financial_years.view',
                'financial_years.create',
                'financial_years.update',
                'financial_years.manage', // buka / tutup / set aktif
            ],
            'Peruntukan & Bajet' => [
                'allocations.view',
                'budget.view_all',   // paparan bajet keseluruhan (pengurusan/kewangan)
                // URS v1.2 Fasa 7.12 — Admin JP set terus (tanpa maker-checker)
                'allocations.manage',
            ],
            'Governance Bajet (Maker-Checker — legacy)' => [
                // MAKER — cadangan peruntukan awal
                'allocations.request.create',
                'allocations.request.update',
                'allocations.request.submit',
                // CHECKER — kelulusan peruntukan awal
                'allocations.approve',
                'allocations.reject',
                'allocations.return',
                // MAKER — cadangan pelarasan
                'adjustments.request.create',
                'adjustments.request.update',
                'adjustments.request.submit',
                // CHECKER — kelulusan pelarasan
                'adjustments.approve',
                'adjustments.reject',
                'adjustments.return',
            ],
            'Permohonan' => [
                'applications.create',    // ALP / Urus Setia ALP mencipta permohonan sendiri
                'applications.create_on_behalf', // Admin JP isi borang bagi pihak ALP
                'applications.view_all',  // staf DBKL melihat semua permohonan
            ],
            'Semakan & Kelulusan' => [
                'applications.review.secretariat',
                'applications.review.finance',
                'applications.review.technical',
                'applications.approve',
                'applications.reject',
                'approval_matrix.view',
                'approval_matrix.manage',
            ],
            'Projek & Perbelanjaan' => [
                'projects.view',
                'projects.view_all',
                'projects.update',
                'projects.progress',
                'projects.milestones',
                'projects.complete',
                'projects.close',
                'expenses.view',
                'expenses.create',
                'expenses.update',
                'expenses.submit',
                'expenses.verify',
                'expenses.reject',
                'expenses.return',
                'expenses.documents.view',
                'expenses.documents.manage',
                'refunds.view',
                'refunds.create',
                'refunds.update',
                'refunds.submit',
                'refunds.verify',
                'refunds.reject',
                'refunds.return',
                'projects.closure-documents.view',
                'projects.closure-documents.manage',
                'project-reports.create',
                'project-reports.review',
            ],
            'Laporan & Dashboard' => [
                'dashboard.finance',
                'reports.view',
                'reports.financial',
                'reports.applications',
                'reports.projects',
                'reports.csr',
                'reports.audit',
                'reports.export',
            ],
            'Pembayaran / Baucar (URS)' => [
                'payments.view',
                'payments.manage',
                // Kemaskini baucar selepas direkod — Super Admin sahaja.
                'payments.voucher_edit',
                // SEC-007: tanpa payments.manage → senarai/eksport terhad rekod dihantar JKEW.
                'payments.jkew_scope',
            ],
            'Tetapan Sistem' => [
                'settings.manage',
            ],
        ];
    }

    /** Semua permission sebagai satu senarai rata. */
    public static function all(): array
    {
        return array_merge(...array_values(static::groups()));
    }

    /**
     * Pemetaan peranan → senarai permission.
     * @return array<string, list<string>>
     */
    public static function forRoles(): array
    {
        return [
            RoleName::SUPER_ADMIN->value => static::all(),

            RoleName::SYSTEM_ADMIN->value => [
                'dashboard.view',
                'users.view', 'users.create', 'users.update', 'users.deactivate', 'users.assign_role',
                'roles.view',
                'alps.view', 'alps.create', 'alps.update', 'alps.deactivate',
                'financial_years.view', 'financial_years.create', 'financial_years.update', 'financial_years.manage',
                // URS v1.2: Admin JP set peruntukan terus (Fasa 7.12).
                'allocations.view', 'budget.view_all', 'allocations.manage',
                'applications.view_all',
                // Admin JP boleh isi Borang Penyaluran bagi pihak ALP (termasuk notis pendek < 2 bulan).
                'applications.create_on_behalf',
                // Keputusan Semakan JP (Disyorkan / Tidak Disyorkan / Pulangkan).
                'applications.review.secretariat',
                'approval_matrix.view', 'approval_matrix.manage',
                'projects.view_all',
                'expenses.documents.view', 'refunds.view', 'projects.closure-documents.view',
                'reports.view', 'reports.applications', 'reports.projects', 'reports.audit', 'reports.export',
                'payments.view',
                'settings.manage',
            ],

            RoleName::ALP->value => [
                'dashboard.view',
                'applications.create',
                'projects.view',
                // Laporan skop-sendiri (dikuatkuasa di controller kepada alp_id sendiri).
                'reports.view', 'reports.export',
            ],

            RoleName::URUSSETIA_ALP->value => [
                'dashboard.view',
                'alps.view',
                'applications.create',
                'projects.view',
                'reports.view', 'reports.export',
            ],

            RoleName::PEGAWAI_URUSSETIA->value => [ // PROJECT OPERATOR
                'dashboard.view',
                'alps.view',
                // Ringkasan peruntukan (baca sahaja) — rujukan baki ALP semasa membuat pengesyoran.
                'allocations.view',
                'applications.view_all',
                'applications.review.secretariat',
                // Paparan senarai pembayaran/baucar (baca sahaja — sama TP/Pengarah).
                'payments.view',
                'projects.view_all', 'projects.update', 'projects.progress', 'projects.milestones',
                'projects.complete', 'project-reports.create', 'expenses.view',
                'expenses.documents.view', 'refunds.view',
                'projects.closure-documents.view', 'projects.closure-documents.manage',
                'reports.view', 'reports.applications', 'reports.projects', 'reports.csr', 'reports.export',
            ],

            RoleName::PEGAWAI_KEWANGAN->value => [ // FINANCE MAKER / Kerani baucar
                'dashboard.view',
                'alps.view',
                'allocations.view',
                'budget.view_all',
                'applications.view_all',
                'applications.review.finance',
                // Maker: cipta/hantar cadangan (tidak boleh lulus sendiri).
                'allocations.request.create', 'allocations.request.update', 'allocations.request.submit',
                'adjustments.request.create', 'adjustments.request.update', 'adjustments.request.submit',
                // Perbelanjaan projek — MAKER.
                'projects.view_all', 'expenses.view', 'expenses.create', 'expenses.update', 'expenses.submit',
                'expenses.documents.view', 'expenses.documents.manage',
                // Refund — MAKER.
                'refunds.view', 'refunds.create', 'refunds.update', 'refunds.submit',
                'projects.closure-documents.view',
                // Laporan & dashboard kewangan.
                'dashboard.finance', 'reports.view', 'reports.financial', 'reports.projects', 'reports.export',
                // Pembayaran/baucar URS (Kerani — barisan penuh).
                'payments.view', 'payments.manage',
            ],

            RoleName::PEGAWAI_JKEW->value => [
                'dashboard.view',
                'applications.view_all',
                // SEC-007: skop JKEW — tanpa payments.manage.
                'payments.view', 'payments.jkew_scope',
                'reports.view', 'reports.applications', 'reports.export',
            ],

            RoleName::PEGAWAI_TEKNIKAL->value => [
                'dashboard.view',
                'alps.view',
                'applications.view_all',
                'applications.review.technical',
                'projects.view_all',
                'expenses.documents.view', 'refunds.view', 'projects.closure-documents.view',
                'reports.view', 'reports.projects',
            ],

            RoleName::PELULUS->value => [ // FINANCE CHECKER (+ pelulus permohonan)
                'dashboard.view',
                'alps.view',
                'allocations.view',
                'budget.view_all',
                'applications.view_all',
                'applications.approve',
                'applications.reject',
                // Checker: lulus/tolak/kembali cadangan bajet (maker≠checker dikuatkuasa).
                'allocations.approve', 'allocations.reject', 'allocations.return',
                'adjustments.approve', 'adjustments.reject', 'adjustments.return',
                // Perbelanjaan projek — CHECKER + penutupan projek.
                'projects.view_all', 'expenses.view', 'expenses.verify', 'expenses.reject', 'expenses.return',
                'expenses.documents.view',
                // Refund — CHECKER.
                'refunds.view', 'refunds.verify', 'refunds.reject', 'refunds.return',
                'projects.closure-documents.view',
                'projects.close', 'project-reports.review',
                // Laporan & dashboard kewangan.
                'dashboard.finance', 'reports.view', 'reports.financial', 'reports.projects', 'reports.export',
                'payments.view',
            ],

            RoleName::PENGURUSAN->value => [
                'dashboard.view',
                'dashboard.executive',
                'alps.view',
                'financial_years.view',
                'allocations.view',
                'budget.view_all',
                'applications.view_all',
                'applications.approve',
                'applications.reject',
                'approval_matrix.view',
                'projects.view_all',
                'expenses.documents.view', 'refunds.view', 'projects.closure-documents.view',
                // Dashboard eksekutif + semua laporan (pengurusan).
                'dashboard.finance',
                'reports.view', 'reports.financial', 'reports.applications', 'reports.projects',
                'reports.csr', 'reports.audit', 'reports.export',
                'payments.view',
            ],
        ];
    }
}
