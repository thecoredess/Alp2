<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cadangan bajet (maker-checker) untuk peruntukan awal & pelarasan.
 * TIADA kesan ledger sehingga diluluskan (status APPROVED → transaksi diposkan).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budget_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_type', 30); // initial_allocation | allocation_increase | allocation_decrease
            $table->foreignId('alp_id')->constrained('alps')->restrictOnDelete();
            $table->foreignId('financial_year_id')->constrained('financial_years')->restrictOnDelete();

            $table->decimal('amount', 15, 2); // magnitud positif; arah ditentukan oleh request_type
            $table->string('status', 30)->default('draft');
            $table->text('reason')->nullable();
            $table->string('reference_number', 100)->nullable();
            $table->unsignedInteger('revision_number')->default(0);

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();   // MAKER
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();

            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();   // CHECKER
            $table->timestamp('approved_at')->nullable();

            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->foreignId('returned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('returned_at')->nullable();
            $table->text('return_reason')->nullable();

            $table->timestamps();

            $table->index(['alp_id', 'financial_year_id']);
            $table->index('status');
            $table->index('request_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_requests');
    }
};
