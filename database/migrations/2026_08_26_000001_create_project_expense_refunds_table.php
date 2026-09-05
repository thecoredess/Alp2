<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Refund / pemulangan bagi perbelanjaan projek yang telah DISAHKAN (maker-checker).
 * TIADA kesan ledger sehingga VERIFIED (barulah transaksi REFUND diposkan).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_expense_refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_expense_id')->constrained('project_expenses')->cascadeOnDelete();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();

            $table->decimal('amount', 15, 2);
            $table->text('reason')->nullable();
            $table->string('reference_number', 100)->nullable();
            $table->date('refund_date');
            $table->string('status', 30)->default('draft');
            $table->unsignedInteger('revision_number')->default(0);

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();

            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();

            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->foreignId('returned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('returned_at')->nullable();
            $table->text('return_reason')->nullable();

            $table->timestamps();

            $table->index(['project_expense_id', 'status']);
            $table->index('project_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_expense_refunds');
    }
};
