<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kaitkan transaksi ledger dengan refund. Unique(project_expense_refund_id)
 * menjamin idempotensi: satu refund disahkan = SATU transaksi REFUND.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('budget_transactions', function (Blueprint $table) {
            $table->foreignId('project_expense_refund_id')->nullable()->after('project_expense_id')
                ->constrained('project_expense_refunds')->nullOnDelete();
            $table->unique('project_expense_refund_id', 'bt_refund_unique');
        });
    }

    public function down(): void
    {
        Schema::table('budget_transactions', function (Blueprint $table) {
            $table->dropUnique('bt_refund_unique');
            $table->dropConstrainedForeignId('project_expense_refund_id');
        });
    }
};
