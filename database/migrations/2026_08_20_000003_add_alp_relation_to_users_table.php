<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Kaitan pengguna dengan ALP (untuk role ALP & Urus Setia ALP).
            $table->foreignId('alp_id')->nullable()->after('unit')
                ->constrained('alps')->nullOnDelete();
            // Siapa mencipta akaun ini (pentadbir).
            $table->foreignId('created_by')->nullable()->after('alp_id')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('alp_id');
            $table->dropConstrainedForeignId('created_by');
        });
    }
};
