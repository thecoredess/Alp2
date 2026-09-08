<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** M07 — semakan laporan aktiviti: Admin JP → Pegawai JP. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->string('report_card_status', 30)->nullable()->after('report_card_submitted_at');
        });

        Schema::create('report_card_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('applications')->cascadeOnDelete();
            $table->foreignId('reviewer_id')->constrained('users');
            $table->string('stage', 20);
            $table->string('decision', 30);
            $table->text('comments')->nullable();
            $table->timestamp('reviewed_at');
            $table->timestamp('created_at')->nullable();

            $table->index(['application_id', 'stage']);
        });

        // Rekod lama yang sudah dimuat naik dianggap disahkan (elak queue berlebihan).
        DB::table('applications')
            ->whereNotNull('report_card_submitted_at')
            ->whereNull('report_card_status')
            ->update(['report_card_status' => 'approved']);
    }

    public function down(): void
    {
        Schema::dropIfExists('report_card_reviews');

        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn('report_card_status');
        });
    }
};
