<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pendaftaran_room_selections', function (Blueprint $table) {
            $table->string('status', 20)->default('approved')->after('pendaftaran_kamar_id'); // pending, approved, rejected
            $table->timestamp('approved_at')->nullable()->after('status');
            $table->text('notes')->nullable()->after('approved_at');
        });
    }

    public function down(): void
    {
        Schema::table('pendaftaran_room_selections', function (Blueprint $table) {
            $table->dropColumn(['status', 'approved_at', 'notes']);
        });
    }
};
