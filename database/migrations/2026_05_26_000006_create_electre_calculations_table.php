<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('electre_calculations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tahun_perencanaan_id')->constrained('tahun_perencanaans')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('kode_perhitungan')->unique();
            $table->string('judul')->nullable();
            $table->text('deskripsi')->nullable();
            $table->string('status')->default('draft')->index();
            $table->unsignedInteger('versi')->default(1);
            $table->boolean('is_latest')->default(true)->index();
            $table->unsignedInteger('total_alternatif')->default(0);
            $table->unsignedInteger('total_kriteria')->default(0);
            $table->foreignId('calculated_by')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamp('calculated_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['tahun_perencanaan_id', 'status']);
            $table->index(['tahun_perencanaan_id', 'is_latest']);
            $table->unique(['tahun_perencanaan_id', 'versi'], 'electre_calculation_periode_versi_unique');
        });

        Schema::table('tahun_perencanaans', function (Blueprint $table) {
            $table->foreign('last_electre_calculation_id')->references('id')->on('electre_calculations')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tahun_perencanaans', fn (Blueprint $table) => $table->dropForeign(['last_electre_calculation_id']));
        Schema::dropIfExists('electre_calculations');
    }
};
