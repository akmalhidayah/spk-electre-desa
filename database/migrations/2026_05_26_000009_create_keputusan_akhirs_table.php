<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('keputusan_akhirs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('electre_calculation_id')
                ->unique('keputusan_akhirs_calculation_unique')
                ->constrained('electre_calculations')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->foreignId('electre_result_id')
                ->nullable()
                ->constrained('electre_results')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->foreignId('usulan_pembangunan_id')
                ->constrained('usulan_pembangunans')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->foreignId('tahun_perencanaan_id')->constrained('tahun_perencanaans')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('nomor_keputusan', 100)->nullable();
            $table->date('tanggal_keputusan')->nullable();
            $table->string('status')->default('draft')->index();
            $table->text('dasar_pertimbangan')->nullable();
            $table->text('catatan_keputusan')->nullable();
            $table->longText('tanda_tangan')->nullable();
            $table->json('snapshot_data')->nullable();
            $table->string('pdf_path')->nullable();
            $table->string('pdf_hash', 64)->nullable();
            $table->timestamp('snapshotted_at')->nullable();
            $table->foreignId('ditetapkan_oleh')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();

            $table->index(['electre_calculation_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('keputusan_akhirs');
    }
};
