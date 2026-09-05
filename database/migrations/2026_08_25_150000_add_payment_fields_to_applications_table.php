<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->string('payment_status', 40)->nullable()->after('submitted_at')->index();
            $table->string('payment_voucher_no', 100)->nullable()->after('payment_status');
            $table->string('payment_reference', 150)->nullable()->after('payment_voucher_no');
            $table->timestamp('paid_at')->nullable()->after('payment_reference');
            $table->text('payment_remarks')->nullable()->after('paid_at');
            $table->foreignId('payment_updated_by')->nullable()->after('payment_remarks')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('payment_updated_at')->nullable()->after('payment_updated_by');
        });

        // Permohonan sedia ada yang diluluskan → menunggu pembayaran.
        DB::table('applications')
            ->where('status', 'approved')
            ->whereNull('payment_status')
            ->update(['payment_status' => 'pending_payment']);
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropConstrainedForeignId('payment_updated_by');
            $table->dropColumn([
                'payment_status',
                'payment_voucher_no',
                'payment_reference',
                'paid_at',
                'payment_remarks',
                'payment_updated_at',
            ]);
        });
    }
};
