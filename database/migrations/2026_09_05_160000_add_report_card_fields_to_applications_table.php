<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** M07 report card — jejak muat naik & peringatan NT-007. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->timestamp('report_card_submitted_at')->nullable()->after('jkew_crosscheck_remarks');
            $table->timestamp('report_card_reminder_sent_at')->nullable()->after('report_card_submitted_at');
            $table->text('report_card_remarks')->nullable()->after('report_card_reminder_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn([
                'report_card_submitted_at',
                'report_card_reminder_sent_at',
                'report_card_remarks',
            ]);
        });
    }
};
