<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Perbelanjaan projek (maker-checker). TIADA kesan ledger sehingga VERIFIED.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->date('expense_date');
            $table->string('reference_number', 100);
            $table->string('payee')->nullable();
            $table->string('description');
            $table->decimal('amount', 15, 2);
            $table->string('status', 30)->default('draft');
            $table->unsignedInteger('revision_number')->default(0);

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();  // MAKER
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();

            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();  // CHECKER
            $table->timestamp('verified_at')->nullable();

            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->foreignId('returned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('returned_at')->nullable();
            $table->text('return_reason')->nullable();

            $table->timestamps();

            $table->index(['project_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_expenses');
    }
};
