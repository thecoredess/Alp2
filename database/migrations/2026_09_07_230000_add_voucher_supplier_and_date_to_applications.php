<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->string('payment_supplier_no', 100)->nullable()->after('payment_voucher_no');
            $table->date('payment_voucher_date')->nullable()->after('payment_supplier_no');
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn(['payment_supplier_no', 'payment_voucher_date']);
        });
    }
};
