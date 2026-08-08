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
            $table->foreignId('tahun_perencanaan_id')->constrained('tahun_perencanaans')->cascadeOnUpdate()->restrictOnDelete();
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
            $table->string('sumber_dokumen')->nullable();
            $table->string('nomor_dokumen')->nullable();
            $table->string('kategori_kegiatan', 100)->nullable()->index();
            $table->unsignedInteger('jumlah_usulan')->nullable();
            $table->decimal('estimasi_anggaran', 15, 2)->nullable();
            $table->decimal('anggaran_realisasi', 15, 2)->nullable();
            $table->text('deskripsi')->nullable();
            $table->text('kondisi_awal')->nullable();
            $table->string('status_usulan')->default('diajukan')->index();
            $table->string('status_pelaksanaan')->default('belum_dilaksanakan')->index();
            $table->unsignedSmallInteger('tahun_realisasi')->nullable();
            $table->text('catatan_admin')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tahun_perencanaan_id', 'dusun_id']);
            $table->index(['tahun_perencanaan_id', 'status_usulan']);
            $table->index(['tahun_perencanaan_id', 'tipe_usulan']);
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
