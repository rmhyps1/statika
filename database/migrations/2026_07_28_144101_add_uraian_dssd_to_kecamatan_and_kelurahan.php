<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kecamatan', function (Blueprint $table) {
            $table->text('uraian_dssd')->nullable()->after('nama_kecamatan');
        });

        Schema::table('kelurahan', function (Blueprint $table) {
            $table->text('uraian_dssd')->nullable()->after('nama_kelurahan');
        });
    }

    public function down(): void
    {
        Schema::table('kecamatan', function (Blueprint $table) {
            $table->dropColumn('uraian_dssd');
        });

        Schema::table('kelurahan', function (Blueprint $table) {
            $table->dropColumn('uraian_dssd');
        });
    }
};
