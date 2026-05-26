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
        Schema::create('kriterias', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();
            $table->string('nama_kriteria');
            $table->decimal('bobot', 5, 2);
            $table->string('tipe')->default('benefit')->index();
            $table->text('deskripsi')->nullable();
            $table->unsignedInteger('urutan')->default(0)->index();
            $table->string('status')->default('aktif')->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'urutan']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kriterias');
    }
};
