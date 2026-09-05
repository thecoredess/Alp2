<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kaitkan transaksi ledger dengan permohonan (untuk COMMITMENT).
 * Unique(application_id, type) menjamin idempotensi: satu permohonan hanya
 * boleh mempunyai SATU transaksi COMMITMENT (klik berganda / serentak tidak
 * boleh mencipta komitmen berganda). NULL application_id dibenarkan berbilang
 * (cth peruntukan/pelarasan tidak dikaitkan permohonan).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('budget_transactions', function (Blueprint $table) {
            $table->foreignId('application_id')->nullable()->after('financial_year_id')
                ->constrained('applications')->nullOnDelete();
            $table->unique(['application_id', 'type'], 'bt_application_type_unique');
        });
    }

    public function down(): void
    {
        Schema::table('budget_transactions', function (Blueprint $table) {
            $table->dropUnique('bt_application_type_unique');
            $table->dropConstrainedForeignId('application_id');
        });
    }
};
