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
        Schema::table('imported_dssd_data', function (Blueprint $table) {
            $table->string('satuan')->nullable()->after('ketersediaan_source');
            $table->text('definisi_operasional')->nullable()->after('satuan');
            $table->string('tag_urusan')->nullable()->after('definisi_operasional');
            $table->string('info_sub_kegiatan')->nullable()->after('tag_urusan');
            $table->text('keterangan')->nullable()->after('info_sub_kegiatan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('imported_dssd_data', function (Blueprint $table) {
            $table->dropColumn([
                'satuan',
                'definisi_operasional',
                'tag_urusan',
                'info_sub_kegiatan',
                'keterangan'
            ]);
        });
    }
};
