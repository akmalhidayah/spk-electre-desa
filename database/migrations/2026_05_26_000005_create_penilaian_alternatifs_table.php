<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penilaian_alternatifs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tahun_perencanaan_id')->constrained('tahun_perencanaans')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('usulan_pembangunan_id')->constrained('usulan_pembangunans')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('kriteria_id')->constrained('kriterias')->cascadeOnUpdate()->restrictOnDelete();
            $table->unsignedTinyInteger('nilai');
            $table->text('keterangan')->nullable();
            $table->string('sumber_data')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamps();
            $table->unique(['tahun_perencanaan_id', 'usulan_pembangunan_id', 'kriteria_id'], 'penilaian_periode_program_kriteria_unique');
            $table->index(['tahun_perencanaan_id', 'usulan_pembangunan_id'], 'penilaian_periode_program_index');
            $table->index(['tahun_perencanaan_id', 'kriteria_id'], 'penilaian_periode_kriteria_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penilaian_alternatifs');
    }
};
