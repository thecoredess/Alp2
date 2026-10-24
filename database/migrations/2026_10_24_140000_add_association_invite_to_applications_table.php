<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->string('association_invite_token', 64)->nullable()->unique()->after('updated_by');
            $table->timestamp('association_invite_expires_at')->nullable()->after('association_invite_token');
            $table->timestamp('association_invite_filled_at')->nullable()->after('association_invite_expires_at');
            $table->foreignId('association_invite_issued_by')->nullable()->after('association_invite_filled_at')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropConstrainedForeignId('association_invite_issued_by');
            $table->dropColumn([
                'association_invite_token',
                'association_invite_expires_at',
                'association_invite_filled_at',
            ]);
        });
    }
};
