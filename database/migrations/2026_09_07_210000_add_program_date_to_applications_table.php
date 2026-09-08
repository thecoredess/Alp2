<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('applications', 'program_date')) {
            Schema::table('applications', function (Blueprint $table) {
                $table->date('program_date')->nullable()->after('recipient_ros_number');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('applications', 'program_date')) {
            Schema::table('applications', function (Blueprint $table) {
                $table->dropColumn('program_date');
            });
        }
    }
};
