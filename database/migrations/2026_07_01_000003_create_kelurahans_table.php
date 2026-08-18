<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kelurahan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kecamatan_id')->constrained('kecamatan')->cascadeOnDelete();
            $table->string('kode_kelurahan')->unique();
            $table->string('nama_kelurahan');
            $table->string('produsen_data')->nullable();
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
        Schema::dropIfExists('kelurahan');
    }
};
