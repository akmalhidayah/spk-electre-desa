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
                ->nullable()
                ->constrained('dusuns')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->unsignedSmallInteger('tahun');
            $table->string('nama_kegiatan');
            $table->string('tipe_usulan')->default('dusun')->index();
            $table->string('lokasi_kegiatan')->nullable();
            $table->decimal('prakiraan_volume', 12, 2)->nullable();
            $table->string('satuan', 50)->nullable();
            $table->unsignedInteger('penerima_manfaat_lk')->nullable();
            $table->unsignedInteger('penerima_manfaat_pr')->nullable();
            $table->unsignedInteger('penerima_manfaat_a_rtm')->nullable();
            $table->string('sdgs_ke')->nullable();
            $table->string('sumber_usulan')->nullable();
            $table->string('kategori_kegiatan', 100)->nullable()->index();
            $table->unsignedInteger('jumlah_usulan')->nullable();
            $table->decimal('estimasi_anggaran', 15, 2)->nullable();
            $table->text('deskripsi')->nullable();
            $table->string('status')->default('diajukan')->index();
            $table->string('status_prioritas')->default('non_prioritas')->index();
            $table->boolean('is_data_pendukung_penilaian')->default(false)->index();
            $table->text('catatan_admin')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tahun', 'dusun_id']);
            $table->index(['tahun', 'status']);
            $table->index(['tahun', 'tipe_usulan']);
            $table->index(['tahun', 'is_data_pendukung_penilaian'], 'usulan_tahun_pendukung_index');
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
