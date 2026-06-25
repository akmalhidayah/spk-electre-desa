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
        Schema::create('electre_calculations', function (Blueprint $table) {
            $table->id();
            $table->string('kode_perhitungan')->unique();
            $table->unsignedSmallInteger('tahun')->index();
            $table->string('judul')->nullable();
            $table->text('deskripsi')->nullable();
            $table->string('status')->default('draft')->index();
            $table->unsignedInteger('versi')->default(1);
            $table->boolean('is_latest')->default(true)->index();
            $table->unsignedInteger('total_alternatif')->default(0);
            $table->unsignedInteger('total_kriteria')->default(0);
            $table->foreignId('calculated_by')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->timestamp('calculated_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tahun', 'status']);
            $table->index(['tahun', 'is_latest']);
            $table->unique(['tahun', 'versi'], 'electre_calculation_tahun_versi_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('electre_calculations');
    }
};
