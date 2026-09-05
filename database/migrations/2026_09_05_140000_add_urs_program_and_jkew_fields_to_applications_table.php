<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fasa 7.5–7.8: BR-014 short notice, M06 JKEW, deklarasi BR-012.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->boolean('is_short_notice')->default(false)->after('proposed_end_date');
            $table->timestamp('compliance_declared_at')->nullable()->after('is_short_notice');

            $table->timestamp('sent_to_jkew_at')->nullable()->after('payment_updated_at');
            $table->string('jkew_crosscheck_status', 32)->nullable()->after('sent_to_jkew_at');
            $table->text('jkew_crosscheck_remarks')->nullable()->after('jkew_crosscheck_status');
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn([
                'is_short_notice',
                'compliance_declared_at',
                'sent_to_jkew_at',
                'jkew_crosscheck_status',
                'jkew_crosscheck_remarks',
            ]);
        });
    }
};
