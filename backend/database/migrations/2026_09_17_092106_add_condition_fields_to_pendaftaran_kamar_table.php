<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('pendaftaran_kamar', function (Blueprint $table) {
            $table->string('status_kondisi', 30)->default('Baik')->after('current_occupancy');
            $table->text('catatan_fasilitas')->nullable()->after('status_kondisi');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('pendaftaran_kamar', function (Blueprint $table) {
            $table->dropColumn(['status_kondisi', 'catatan_fasilitas']);
        });
    }
};
