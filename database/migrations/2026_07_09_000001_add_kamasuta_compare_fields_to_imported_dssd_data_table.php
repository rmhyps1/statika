<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('imported_dssd_data', function (Blueprint $table) {
            $table->string('ketersediaan_source')->default('manual')->after('ketersediaan_data');
            $table->string('matched_kamasuta_code')->nullable()->after('ketersediaan_source');
            $table->text('matched_kamasuta_title')->nullable()->after('matched_kamasuta_code');
            $table->string('matched_by')->nullable()->after('matched_kamasuta_title');
            $table->timestamp('last_compared_at')->nullable()->after('matched_by');
        });
    }

    public function down(): void
    {
        Schema::table('imported_dssd_data', function (Blueprint $table) {
            $table->dropColumn([
                'ketersediaan_source',
                'matched_kamasuta_code',
                'matched_kamasuta_title',
                'matched_by',
                'last_compared_at',
            ]);
        });
    }
};
