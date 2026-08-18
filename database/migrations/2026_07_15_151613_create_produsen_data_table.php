<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produsen_data', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->integer('jumlah_disepakati')->default(0);
            $table->integer('jumlah_dilaporkan')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produsen_data');
    }
};
