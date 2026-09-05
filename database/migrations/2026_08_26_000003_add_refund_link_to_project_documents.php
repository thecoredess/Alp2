<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Membenarkan dokumen projek dikaitkan dengan refund (bukti refund). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_documents', function (Blueprint $table) {
            $table->foreignId('project_expense_refund_id')->nullable()->after('project_expense_id')
                ->constrained('project_expense_refunds')->cascadeOnDelete();
            $table->index('project_expense_refund_id');
        });
    }

    public function down(): void
    {
        Schema::table('project_documents', function (Blueprint $table) {
            $table->dropConstrainedForeignId('project_expense_refund_id');
        });
    }
};
