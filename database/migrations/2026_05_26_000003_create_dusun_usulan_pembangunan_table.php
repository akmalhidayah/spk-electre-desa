<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dusun_usulan_pembangunan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usulan_pembangunan_id')
                ->constrained('usulan_pembangunans')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('dusun_id')
                ->constrained('dusuns')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['usulan_pembangunan_id', 'dusun_id'], 'dusun_usulan_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dusun_usulan_pembangunan');
    }
};
