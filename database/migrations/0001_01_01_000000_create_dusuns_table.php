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
        Schema::create('dusuns', function (Blueprint $table) {
            $table->id();
            $table->string('kode_alternatif')->nullable()->unique();
            $table->string('nama_dusun');
            $table->decimal('luas_tanah', 12, 2)->nullable();
            $table->unsignedInteger('jumlah_penduduk')->nullable();
            $table->text('keterangan')->nullable();
            $table->string('status')->default('aktif')->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index('nama_dusun');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dusuns');
    }
};
