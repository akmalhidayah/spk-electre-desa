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
        Schema::create('penilaian_alternatifs', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('tahun');
            $table->foreignId('dusun_id')
                ->constrained('dusuns')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->foreignId('kriteria_id')
                ->constrained('kriterias')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->unsignedTinyInteger('nilai');
            $table->text('keterangan')->nullable();
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->timestamps();

            $table->unique(['tahun', 'dusun_id', 'kriteria_id'], 'penilaian_tahun_dusun_kriteria_unique');
            $table->index(['tahun', 'dusun_id']);
            $table->index(['tahun', 'kriteria_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penilaian_alternatifs');
    }
};
