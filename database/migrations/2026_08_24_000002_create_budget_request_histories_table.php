<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Sejarah peralihan status cadangan bajet — append-only. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budget_request_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_request_id')->constrained('budget_requests')->cascadeOnDelete();
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30);
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('remarks')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index('budget_request_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_request_histories');
    }
};
