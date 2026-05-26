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
        Schema::create('usulan_pembangunans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dusun_id')
                ->constrained('dusuns')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->unsignedSmallInteger('tahun');
            $table->string('nama_kegiatan');
            $table->unsignedInteger('jumlah_usulan')->nullable();
            $table->decimal('estimasi_anggaran', 15, 2)->nullable();
            $table->text('deskripsi')->nullable();
            $table->string('status')->default('diajukan')->index();
            $table->text('catatan_admin')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tahun', 'dusun_id']);
            $table->index(['tahun', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usulan_pembangunans');
    }
};
