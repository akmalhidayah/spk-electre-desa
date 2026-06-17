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
        Schema::create('struktur_organisasi_desas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('welcome_desa_setting_id')
                ->nullable()
                ->constrained('welcome_desa_settings')
                ->nullOnDelete();
            $table->string('nama', 150);
            $table->string('jabatan', 150);
            $table->string('foto')->nullable();
            $table->text('deskripsi')->nullable();
            $table->integer('urutan')->default(0)->index();
            $table->boolean('status_aktif')->default(true)->index();
            $table->timestamps();

            $table->index(['welcome_desa_setting_id', 'status_aktif', 'urutan'], 'struktur_setting_status_urutan_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('struktur_organisasi_desas');
    }
};
