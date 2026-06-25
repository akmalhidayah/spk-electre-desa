<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tahun_perencanaans', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('tahun')->unique();
            $table->string('nama_periode')->nullable();
            $table->text('deskripsi')->nullable();
            $table->boolean('is_active')->default(false)->index();
            $table->boolean('is_locked')->default(false)->index();
            $table->boolean('perlu_hitung_ulang')->default(false)->index();
            $table->text('alasan_hitung_ulang')->nullable();
            $table->timestamp('last_data_changed_at')->nullable();
            $table->foreignId('last_electre_calculation_id')
                ->nullable()
                ->constrained('electre_calculations')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tahun_perencanaans');
    }
};
