<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->string('application_number', 40)->unique();
            $table->foreignId('financial_year_id')->constrained('financial_years')->restrictOnDelete();
            $table->foreignId('alp_id')->constrained('alps')->restrictOnDelete();
            $table->string('application_type', 20);

            $table->string('project_title');
            $table->text('project_summary')->nullable();
            $table->text('objectives')->nullable();
            $table->text('scope')->nullable();
            $table->string('target_group')->nullable();
            $table->string('location')->nullable();

            $table->date('proposed_start_date')->nullable();
            $table->date('proposed_end_date')->nullable();

            // Snapshot dikira backend; tidak boleh ditaip pengguna.
            $table->decimal('requested_amount', 15, 2)->default(0);
            $table->string('status', 30)->default('draft');

            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['alp_id', 'financial_year_id']);
            $table->index('status');
            $table->index('application_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
