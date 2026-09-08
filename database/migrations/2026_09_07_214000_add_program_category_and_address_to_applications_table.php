<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            if (! Schema::hasColumn('applications', 'program_category')) {
                $table->string('program_category', 40)->nullable()->after('program_date');
            }
            if (! Schema::hasColumn('applications', 'recipient_address')) {
                $table->string('recipient_address', 500)->nullable()->after('recipient_bank_account');
            }
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            foreach (['program_category', 'recipient_address'] as $column) {
                if (Schema::hasColumn('applications', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
