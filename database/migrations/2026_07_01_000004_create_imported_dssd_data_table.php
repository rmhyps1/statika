<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('imported_dssd_data', function (Blueprint $table) {
            $table->id();
            $table->string('kode_dssd');
            $table->text('uraian_dssd');
            $table->string('produsen_data')->nullable();
            $table->enum('ketersediaan_data', ['ada', 'tidak'])->default('tidak');
            $table->string('jenis_data')->nullable();
            $table->string('jenis_produsen')->nullable();
            $table->year('tahun')->nullable();
            $table->json('raw_data')->nullable();
            $table->timestamps();

            $table->index('kode_dssd');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('imported_dssd_data');
    }
};
