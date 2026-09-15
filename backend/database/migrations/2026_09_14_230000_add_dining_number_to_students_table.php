<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('dining_number', 20)->nullable()->after('status_pendaftaran');
            $table->string('dining_status', 20)->default('active')->after('dining_number');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['dining_number', 'dining_status']);
        });
    }
};
