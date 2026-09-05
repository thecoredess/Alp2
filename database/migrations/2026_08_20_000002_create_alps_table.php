<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alps', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('ref_code', 50)->unique();            // cth: ALP-01
            $table->string('portfolio_zone')->nullable();        // portfolio / zon / kawasan
            $table->date('appointment_start')->nullable();
            $table->date('appointment_end')->nullable();
            $table->string('status', 20)->default('active')->index(); // active|inactive
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alps');
    }
};
