<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Selaraskan permohonan dengan Borang Penyaluran Sumbangan ALP:
 * a ALP · b penerima · c jumlah · d tujuan · e no. akaun + lampiran senarai semak.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('application_budget_items') && Schema::hasColumn('applications', 'requested_amount')) {
            $driver = Schema::getConnection()->getDriverName();
            if ($driver === 'mysql') {
                DB::statement('
                    UPDATE applications a
                    SET requested_amount = (
                        SELECT COALESCE(SUM(total), a.requested_amount)
                        FROM application_budget_items i
                        WHERE i.application_id = a.id
                    )
                    WHERE EXISTS (
                        SELECT 1 FROM application_budget_items i WHERE i.application_id = a.id
                    )
                    AND (a.requested_amount IS NULL OR a.requested_amount = 0)
                ');
            }
        }

        Schema::table('applications', function (Blueprint $table) {
            if (Schema::hasColumn('applications', 'project_title') && ! Schema::hasColumn('applications', 'purpose')) {
                $table->renameColumn('project_title', 'purpose');
            }
        });

        Schema::table('applications', function (Blueprint $table) {
            foreach ([
                'project_summary',
                'objectives',
                'scope',
                'target_group',
                'location',
                'proposed_start_date',
                'proposed_end_date',
                'is_short_notice',
                'compliance_declared_at',
                'program_category',
                'recipient_address',
            ] as $column) {
                if (Schema::hasColumn('applications', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        if (Schema::hasColumn('applications', 'application_type')) {
            DB::table('applications')->whereIn('application_type', ['csr', 'development'])
                ->update(['application_type' => 'sumbangan']);
        }

        Schema::dropIfExists('application_budget_items');

        if (Schema::hasTable('document_requirements')) {
            Schema::table('document_requirements', function (Blueprint $table) {
                $table->dropUnique(['application_type', 'document_type']);
            });

            Schema::table('document_requirements', function (Blueprint $table) {
                if (Schema::hasColumn('document_requirements', 'application_type')) {
                    $table->dropColumn('application_type');
                }
            });

            $seen = [];
            foreach (DB::table('document_requirements')->orderBy('id')->get() as $row) {
                if (isset($seen[$row->document_type])) {
                    DB::table('document_requirements')->where('id', $row->id)->delete();
                } else {
                    $seen[$row->document_type] = true;
                }
            }

            Schema::table('document_requirements', function (Blueprint $table) {
                $table->unique('document_type');
            });
        }
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            if (Schema::hasColumn('applications', 'purpose') && ! Schema::hasColumn('applications', 'project_title')) {
                $table->renameColumn('purpose', 'project_title');
            }
        });

        Schema::table('applications', function (Blueprint $table) {
            if (! Schema::hasColumn('applications', 'project_summary')) {
                $table->text('project_summary')->nullable();
                $table->text('objectives')->nullable();
                $table->text('scope')->nullable();
                $table->string('target_group')->nullable();
                $table->string('location')->nullable();
                $table->date('proposed_start_date')->nullable();
                $table->date('proposed_end_date')->nullable();
            }
        });

        Schema::create('application_budget_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('applications')->cascadeOnDelete();
            $table->string('description');
            $table->unsignedInteger('quantity')->default(1);
            $table->string('unit', 50)->nullable();
            $table->decimal('unit_cost', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->string('remarks')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }
};
