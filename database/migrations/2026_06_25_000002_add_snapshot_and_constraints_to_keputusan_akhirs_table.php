<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('keputusan_akhirs', 'snapshot_data')) {
            Schema::table('keputusan_akhirs', function (Blueprint $table): void {
                $table->json('snapshot_data')->nullable()->after('tanda_tangan');
                $table->string('pdf_path')->nullable()->after('snapshot_data');
                $table->string('pdf_hash', 64)->nullable()->after('pdf_path');
                $table->timestamp('snapshotted_at')->nullable()->after('pdf_hash');
            });
        }

        $duplicate = DB::table('keputusan_akhirs')
            ->select('electre_calculation_id')
            ->whereNotNull('electre_calculation_id')
            ->groupBy('electre_calculation_id')
            ->havingRaw('COUNT(*) > 1')
            ->first();

        if ($duplicate) {
            throw new RuntimeException('Terdapat keputusan akhir ganda untuk satu perhitungan. Rapikan data sebelum menambah unique constraint.');
        }

        Schema::table('keputusan_akhirs', function (Blueprint $table): void {
            $table->dropForeign(['electre_calculation_id']);
        });

        Schema::table('keputusan_akhirs', function (Blueprint $table): void {
            $table->foreign('electre_calculation_id')
                ->references('id')
                ->on('electre_calculations')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->unique('electre_calculation_id', 'keputusan_akhirs_calculation_unique');
        });
    }

    public function down(): void
    {
        Schema::table('keputusan_akhirs', function (Blueprint $table): void {
            $table->dropUnique('keputusan_akhirs_calculation_unique');
            $table->dropForeign(['electre_calculation_id']);
        });

        Schema::table('keputusan_akhirs', function (Blueprint $table): void {
            $table->foreign('electre_calculation_id')
                ->references('id')
                ->on('electre_calculations')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });

        Schema::table('keputusan_akhirs', function (Blueprint $table): void {
            $table->dropColumn([
                'snapshot_data',
                'pdf_path',
                'pdf_hash',
                'snapshotted_at',
            ]);
        });
    }
};
