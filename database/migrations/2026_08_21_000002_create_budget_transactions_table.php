<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ledger bajet — SUMBER KEBENARAN kewangan.
 * Append-only & immutable: rekod tidak boleh dikemas kini atau dipadam
 * (dikuatkuasakan pada model BudgetTransaction). Pembatalan dibuat melalui
 * transaksi pembalikan (reversal), bukan padam.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budget_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('allocation_id')->constrained('allocations')->cascadeOnDelete();
            // Didenormalkan untuk pertanyaan & integriti.
            $table->foreignId('alp_id')->constrained('alps')->cascadeOnDelete();
            $table->foreignId('financial_year_id')->constrained('financial_years')->cascadeOnDelete();

            $table->string('type', 40);
            // Bertanda: positif menambah, negatif mengurang mengikut jenis.
            $table->decimal('amount', 15, 2);
            $table->string('reference_no', 100)->nullable();
            $table->string('description', 500)->nullable();
            // Pautan pilihan ke entiti masa depan (permohonan/projek).
            $table->json('meta')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            // Hanya created_at — ledger tidak pernah dikemas kini.
            $table->timestamp('created_at')->nullable();

            $table->index(['alp_id', 'financial_year_id']);
            $table->index('financial_year_id');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_transactions');
    }
};
