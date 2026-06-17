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
        Schema::create('welcome_desa_settings', function (Blueprint $table) {
            $table->id();
            $table->string('nama_desa', 150)->nullable();
            $table->string('kecamatan', 150)->nullable();
            $table->string('kabupaten', 150)->nullable();
            $table->string('provinsi', 150)->nullable();
            $table->text('alamat')->nullable();
            $table->string('email', 150)->nullable();
            $table->string('telepon', 50)->nullable();
            $table->string('logo_desa')->nullable();
            $table->string('hero_image')->nullable();
            $table->string('judul_welcome')->nullable();
            $table->text('deskripsi_welcome')->nullable();
            $table->text('visi')->nullable();
            $table->text('misi')->nullable();
            $table->string('judul_infografis')->nullable();
            $table->text('deskripsi_infografis')->nullable();
            $table->text('maps_embed')->nullable();
            $table->string('maps_link')->nullable();
            $table->string('gambar_peta')->nullable();
            $table->boolean('status_aktif')->default(true)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('welcome_desa_settings');
    }
};
