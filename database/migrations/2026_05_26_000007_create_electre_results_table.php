<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('electre_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('electre_calculation_id')
                ->constrained('electre_calculations')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('usulan_pembangunan_id')
                ->constrained('usulan_pembangunans')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->string('kode_alternatif', 20);
            $table->string('nama_program_snapshot');
            $table->string('lokasi_snapshot')->nullable();
            $table->string('nama_dusun_snapshot')->nullable();
            $table->unsignedInteger('ranking')->nullable();
            $table->integer('skor_dominasi')->default(0);
            $table->decimal('total_preferensi', 20, 8)->default(0);
            $table->string('status_prioritas')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->unique(['electre_calculation_id', 'usulan_pembangunan_id'], 'electre_result_calculation_program_unique');
            $table->unique(['electre_calculation_id', 'kode_alternatif'], 'electre_result_calculation_code_unique');
            $table->index(['electre_calculation_id', 'ranking']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('electre_results');
    }
};
