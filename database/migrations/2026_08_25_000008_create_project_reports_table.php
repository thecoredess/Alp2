<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->unique()->constrained('projects')->cascadeOnDelete();
            $table->text('summary')->nullable();
            $table->text('outcome')->nullable();
            $table->unsignedInteger('beneficiary_count')->nullable();   // CSR
            $table->text('impact_summary')->nullable();                 // CSR
            $table->text('completion_summary')->nullable();             // Development
            $table->text('issues')->nullable();
            $table->text('lessons_learned')->nullable();
            $table->text('final_remarks')->nullable();
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_reports');
    }
};
