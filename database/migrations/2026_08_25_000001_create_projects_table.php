<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('project_number', 40)->unique();
            // Satu permohonan diluluskan = tepat satu projek.
            $table->foreignId('application_id')->unique()->constrained('applications')->restrictOnDelete();
            $table->foreignId('alp_id')->constrained('alps')->restrictOnDelete();
            $table->foreignId('financial_year_id')->constrained('financial_years')->restrictOnDelete();

            $table->string('project_name');
            $table->string('project_type', 20);
            // Snapshot tidak boleh ubah daripada application.requested_amount pada kelulusan akhir.
            $table->decimal('approved_amount', 15, 2);

            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->date('actual_start_date')->nullable();
            $table->date('actual_completion_date')->nullable();

            $table->string('status', 20)->default('not_started');
            $table->unsignedTinyInteger('progress_percent')->default(0);

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['alp_id', 'financial_year_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
