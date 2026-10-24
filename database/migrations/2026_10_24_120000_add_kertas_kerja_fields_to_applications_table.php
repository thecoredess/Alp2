<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->text('program_background')->nullable()->after('purpose');
            $table->text('program_objectives')->nullable()->after('program_background');
            $table->text('program_beneficiaries')->nullable()->after('program_objectives');
            $table->text('program_schedule')->nullable()->after('program_beneficiaries');
            $table->text('program_budget_breakdown')->nullable()->after('program_schedule');
            $table->text('program_impact')->nullable()->after('program_budget_breakdown');
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn([
                'program_background',
                'program_objectives',
                'program_beneficiaries',
                'program_schedule',
                'program_budget_breakdown',
                'program_impact',
            ]);
        });
    }
};
