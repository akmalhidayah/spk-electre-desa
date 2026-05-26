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
            $table->foreignId('dusun_id')
                ->constrained('dusuns')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->unsignedInteger('ranking')->nullable();
            $table->integer('skor_dominasi')->default(0);
            $table->string('status_prioritas')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->unique(['electre_calculation_id', 'dusun_id'], 'electre_result_calculation_dusun_unique');
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
