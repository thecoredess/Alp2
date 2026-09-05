<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Header akaun peruntukan — satu rekod per (ALP × Tahun Kewangan).
 * TIADA lajur jumlah/baki: semua nilai kewangan dikira dari ledger
 * (budget_transactions). Rekod ini hanya menyimpan rujukan & metadata.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alp_id')->constrained('alps')->cascadeOnDelete();
            $table->foreignId('financial_year_id')->constrained('financial_years')->cascadeOnDelete();
            $table->string('reference_no', 100)->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['alp_id', 'financial_year_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('allocations');
    }
};
