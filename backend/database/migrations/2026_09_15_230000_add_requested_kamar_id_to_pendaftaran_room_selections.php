<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pendaftaran_room_selections', function (Blueprint $table) {
            $table->foreignId('requested_kamar_id')->nullable()->after('pendaftaran_kamar_id')->constrained('pendaftaran_kamar')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pendaftaran_room_selections', function (Blueprint $table) {
            $table->dropForeign(['requested_kamar_id']);
            $table->dropColumn('requested_kamar_id');
        });
    }
};
