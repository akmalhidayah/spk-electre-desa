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
        Schema::create('electre_result_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('electre_calculation_id')
                ->constrained('electre_calculations')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->string('tahap')->index();
            $table->json('data');
            $table->timestamps();

            $table->unique(['electre_calculation_id', 'tahap'], 'electre_detail_calculation_tahap_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('electre_result_details');
    }
};
