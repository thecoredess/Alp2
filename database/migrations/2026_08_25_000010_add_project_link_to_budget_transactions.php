<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kaitkan transaksi ledger dengan projek & perbelanjaan projek.
 * Unique(project_expense_id) menjamin idempotensi: satu perbelanjaan disahkan
 * hanya menghasilkan SATU transaksi EXPENDITURE.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('budget_transactions', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->after('application_id')
                ->constrained('projects')->nullOnDelete();
            $table->foreignId('project_expense_id')->nullable()->after('project_id')
                ->constrained('project_expenses')->nullOnDelete();
            $table->unique('project_expense_id', 'bt_project_expense_unique');
            $table->index('project_id');
        });
    }

    public function down(): void
    {
        Schema::table('budget_transactions', function (Blueprint $table) {
            $table->dropUnique('bt_project_expense_unique');
            $table->dropConstrainedForeignId('project_expense_id');
            $table->dropConstrainedForeignId('project_id');
        });
    }
};
