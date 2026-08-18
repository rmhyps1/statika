<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dssd_opd', function (Blueprint $table) {
            $table->id();
            $table->string('kode_dssd')->unique();
            $table->text('uraian_dssd');
            $table->string('produsen_data');
            $table->string('jenis_data')->nullable();
            $table->string('jenis_produsen')->nullable();
            $table->year('tahun')->nullable();
            $table->string('satuan')->nullable();
            $table->text('definisi_operasional')->nullable();
            $table->string('tag_urusan')->nullable();
            $table->string('info_sub_kegiatan')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dssd_opd');
    }
};
