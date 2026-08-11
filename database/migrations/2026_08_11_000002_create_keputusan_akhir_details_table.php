<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('keputusan_akhir_details', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('keputusan_akhir_id')->constrained('keputusan_akhirs')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('electre_result_id')->nullable()->constrained('electre_results')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('usulan_pembangunan_id')->constrained('usulan_pembangunans')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('kode_alternatif_snapshot')->nullable();
            $table->string('nama_program_snapshot');
            $table->string('lokasi_snapshot')->nullable();
            $table->string('nama_dusun_snapshot')->nullable();
            $table->unsignedInteger('ranking_snapshot')->nullable();
            $table->integer('skor_dominasi_snapshot')->nullable();
            $table->decimal('estimasi_anggaran_snapshot', 15, 2)->nullable();
            $table->timestamps();
            $table->unique(['keputusan_akhir_id', 'usulan_pembangunan_id'], 'keputusan_detail_program_unique');
            $table->index('usulan_pembangunan_id');
        });

        DB::table('keputusan_akhirs')
            ->whereNotNull('usulan_pembangunan_id')
            ->orderBy('id')
            ->each(function ($keputusan): void {
                $result = $keputusan->electre_result_id
                    ? DB::table('electre_results')->where('id', $keputusan->electre_result_id)->first()
                    : null;
                $program = DB::table('usulan_pembangunans')->where('id', $keputusan->usulan_pembangunan_id)->first();

                if (! $program) {
                    return;
                }

                $snapshotAmount = null;
                if ($result) {
                    $rankingData = DB::table('electre_result_details')
                        ->where('electre_calculation_id', $result->electre_calculation_id)
                        ->where('tahap', 'ranking_summary')
                        ->value('data');
                    $ranking = is_string($rankingData) ? json_decode($rankingData, true) : $rankingData;
                    $rankingItem = collect(is_array($ranking) ? $ranking : [])->firstWhere('usulan_pembangunan_id', $program->id);
                    $snapshotAmount = $rankingItem['estimasi_anggaran'] ?? null;
                }

                DB::table('keputusan_akhir_details')->insertOrIgnore([
                    'keputusan_akhir_id' => $keputusan->id,
                    'electre_result_id' => $result?->id,
                    'usulan_pembangunan_id' => $program->id,
                    'kode_alternatif_snapshot' => $result?->kode_alternatif,
                    'nama_program_snapshot' => $result?->nama_program_snapshot ?? $program->nama_kegiatan,
                    'lokasi_snapshot' => $result?->lokasi_snapshot ?? $program->lokasi_kegiatan,
                    'nama_dusun_snapshot' => $result?->nama_dusun_snapshot,
                    'ranking_snapshot' => $result?->ranking,
                    'skor_dominasi_snapshot' => $result?->skor_dominasi,
                    'estimasi_anggaran_snapshot' => $snapshotAmount ?? $program->estimasi_anggaran,
                    'created_at' => $keputusan->created_at,
                    'updated_at' => $keputusan->updated_at,
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('keputusan_akhir_details');
    }
};
