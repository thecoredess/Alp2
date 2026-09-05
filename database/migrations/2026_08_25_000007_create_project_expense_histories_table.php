<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Sejarah status perbelanjaan projek — append-only. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_expense_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_expense_id')->constrained('project_expenses')->cascadeOnDelete();
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30);
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('remarks')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index('project_expense_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_expense_histories');
    }
};
