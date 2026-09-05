<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Medan penerima/persatuan URS v1.2 (TBL-10 B, E, N, O, P + BR-008/009).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->string('recipient_name')->nullable()->after('project_summary');
            $table->string('recipient_ros_number', 64)->nullable()->after('recipient_name');
            $table->string('recipient_bank_account', 64)->nullable()->after('recipient_ros_number');
            $table->string('recipient_address', 500)->nullable()->after('recipient_bank_account');
            $table->string('program_category', 40)->nullable()->after('recipient_address');
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn([
                'recipient_name',
                'recipient_ros_number',
                'recipient_bank_account',
                'recipient_address',
                'program_category',
            ]);
        });
    }
};
