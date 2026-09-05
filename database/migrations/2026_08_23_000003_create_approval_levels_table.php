<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Matriks kelulusan boleh dikonfig. Ambang adalah DATA PEMBANGUNAN,
 * BUKAN polisi rasmi DBKL. max_amount NULL = tiada had atas (∞).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('financial_year_id')->nullable()->constrained('financial_years')->nullOnDelete();
            $table->string('name');
            $table->decimal('min_amount', 15, 2)->default(0);
            $table->decimal('max_amount', 15, 2)->nullable(); // NULL = ∞
            $table->string('required_role', 50);
            $table->unsignedInteger('sequence');
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['active', 'sequence']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_levels');
    }
};
