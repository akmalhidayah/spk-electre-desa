<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tahun_perencanaans', function (Blueprint $table): void {
            $table->decimal('pagu_anggaran', 15, 2)->nullable()->after('deskripsi');
        });
    }

    public function down(): void
    {
        Schema::table('tahun_perencanaans', function (Blueprint $table): void {
            $table->dropColumn('pagu_anggaran');
        });
    }
};
