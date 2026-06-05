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
                ->constrained('electre_calculations')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('electre_result_id')
                ->nullable()
                ->constrained('electre_results')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->foreignId('dusun_id')
                ->nullable()
                ->constrained('dusuns')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->unsignedSmallInteger('tahun')->nullable()->index();
            $table->string('nomor_keputusan', 100)->nullable();
            $table->date('tanggal_keputusan')->nullable();
            $table->string('status')->default('draft')->index();
            $table->text('dasar_pertimbangan')->nullable();
            $table->text('catatan_keputusan')->nullable();
            $table->text('catatan')->nullable();
            $table->foreignId('ditetapkan_oleh')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->foreignId('decided_by')
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
