<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Entiti penerima/persatuan (CRS-ready, AD-005 = No. ROS).
 * Snapshot medan recipient_* kekal pada applications untuk cetakan TBL-10.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recipients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('ros_number', 100);
            $table->string('bank_account', 100)->nullable();
            $table->text('address')->nullable();
            $table->timestamps();

            $table->unique('ros_number');
        });

        Schema::table('applications', function (Blueprint $table) {
            $table->foreignId('recipient_id')
                ->nullable()
                ->after('alp_id')
                ->constrained('recipients')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropConstrainedForeignId('recipient_id');
        });
        Schema::dropIfExists('recipients');
    }
};
