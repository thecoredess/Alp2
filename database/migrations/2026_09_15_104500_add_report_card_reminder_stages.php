<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** NT-007 — jejak peringatan 7 hari sebelum & selepas tarikh akhir laporan. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->timestamp('report_card_upcoming_reminder_sent_at')
                ->nullable()
                ->after('report_card_reminder_sent_at');
            $table->timestamp('report_card_overdue_reminder_sent_at')
                ->nullable()
                ->after('report_card_upcoming_reminder_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn([
                'report_card_upcoming_reminder_sent_at',
                'report_card_overdue_reminder_sent_at',
            ]);
        });
    }
};
