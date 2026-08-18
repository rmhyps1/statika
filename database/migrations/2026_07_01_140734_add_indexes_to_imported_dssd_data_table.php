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
            $table->index('jenis_data');
            $table->index('jenis_produsen');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('imported_dssd_data', function (Blueprint $table) {
            $table->dropIndex(['jenis_data']);
            $table->dropIndex(['jenis_produsen']);
        });
    }
};
