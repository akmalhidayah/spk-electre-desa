<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('keputusan_akhirs', 'tanda_tangan')) {
            Schema::table('keputusan_akhirs', function (Blueprint $table): void {
                $table->longText('tanda_tangan')->nullable()->after('catatan_keputusan');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('keputusan_akhirs', 'tanda_tangan')) {
            Schema::table('keputusan_akhirs', function (Blueprint $table): void {
                $table->dropColumn('tanda_tangan');
            });
        }
    }
};
