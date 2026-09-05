<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kaitkan transaksi ledger dengan cadangan bajet yang diluluskan.
 * Unique(budget_request_id) menjamin idempotensi: satu cadangan hanya boleh
 * menghasilkan SATU transaksi ledger (klik berganda / serentak tidak boleh
 * mencipta pos berganda). NULL dibenarkan berbilang (rekod bootstrap/sejarah).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('budget_transactions', function (Blueprint $table) {
            $table->foreignId('budget_request_id')->nullable()->after('application_id')
                ->constrained('budget_requests')->nullOnDelete();
            $table->unique('budget_request_id', 'bt_budget_request_unique');
        });
    }

    public function down(): void
    {
        Schema::table('budget_transactions', function (Blueprint $table) {
            $table->dropUnique('bt_budget_request_unique');
            $table->dropConstrainedForeignId('budget_request_id');
        });
    }
};
