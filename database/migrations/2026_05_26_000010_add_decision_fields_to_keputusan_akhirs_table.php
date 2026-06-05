<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('keputusan_akhirs')) {
            return;
        }

        Schema::table('keputusan_akhirs', function (Blueprint $table): void {
            if (! Schema::hasColumn('keputusan_akhirs', 'electre_result_id')) {
                $table->foreignId('electre_result_id')
                    ->nullable()
                    ->after('electre_calculation_id')
                    ->constrained('electre_results')
                    ->cascadeOnUpdate()
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('keputusan_akhirs', 'tahun')) {
                $table->unsignedSmallInteger('tahun')->nullable()->after('dusun_id')->index();
            }

            if (! Schema::hasColumn('keputusan_akhirs', 'nomor_keputusan')) {
                $table->string('nomor_keputusan', 100)->nullable()->after('tahun');
            }

            if (! Schema::hasColumn('keputusan_akhirs', 'tanggal_keputusan')) {
                $table->date('tanggal_keputusan')->nullable()->after('nomor_keputusan');
            }

            if (! Schema::hasColumn('keputusan_akhirs', 'catatan_keputusan')) {
                $table->text('catatan_keputusan')->nullable()->after('dasar_pertimbangan');
            }

            if (! Schema::hasColumn('keputusan_akhirs', 'ditetapkan_oleh')) {
                $table->foreignId('ditetapkan_oleh')
                    ->nullable()
                    ->after('catatan_keputusan')
                    ->constrained('users')
                    ->cascadeOnUpdate()
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('keputusan_akhirs')) {
            return;
        }

        Schema::table('keputusan_akhirs', function (Blueprint $table): void {
            foreach (['ditetapkan_oleh', 'electre_result_id'] as $column) {
                if (Schema::hasColumn('keputusan_akhirs', $column)) {
                    $table->dropConstrainedForeignId($column);
                }
            }

            foreach (['tahun', 'nomor_keputusan', 'tanggal_keputusan', 'catatan_keputusan'] as $column) {
                if (Schema::hasColumn('keputusan_akhirs', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
