<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const NEW_DESCRIPTION = 'Menggambarkan kondisi akses jalan pada masing-masing dusun sebagai pertimbangan prioritas pembangunan. Semakin buruk kondisi jalan, maka semakin tinggi kebutuhan pembangunan.';

    private const OLD_DESCRIPTION = 'Semakin membutuhkan penanganan karena kondisi topografis, semakin tinggi prioritas.';

    public function up(): void
    {
        if (! Schema::hasTable('kriterias')) {
            return;
        }

        DB::table('kriterias')
            ->where('kode', 'C4')
            ->update([
                'nama_kriteria' => 'Kondisi Jalan',
                'deskripsi' => self::NEW_DESCRIPTION,
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('kriterias')) {
            return;
        }

        DB::table('kriterias')
            ->where('kode', 'C4')
            ->update([
                'nama_kriteria' => 'Kondisi Topografis',
                'deskripsi' => self::OLD_DESCRIPTION,
                'updated_at' => now(),
            ]);
    }
};
