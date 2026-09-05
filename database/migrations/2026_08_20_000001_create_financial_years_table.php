<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_years', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('year')->unique();     // cth: 2026
            $table->string('label')->nullable();                // cth: "Tahun Kewangan 2026"
            $table->string('status', 20)->default('draft')->index(); // draft|open|active|closed
            $table->boolean('is_active')->default(false)->index();    // hanya satu boleh true
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_years');
    }
};
