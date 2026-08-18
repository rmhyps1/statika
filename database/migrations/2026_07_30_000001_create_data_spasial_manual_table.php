<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_spasial_manual', function (Blueprint $table) {
            $table->id();
            $table->string('nama_produsen');
            $table->integer('tahun')->nullable();
            $table->integer('jumlah_spasial')->default(0);
            $table->timestamps();

            $table->unique(['nama_produsen', 'tahun'], 'uniq_spasial_produsen_tahun');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_spasial_manual');
    }
};
