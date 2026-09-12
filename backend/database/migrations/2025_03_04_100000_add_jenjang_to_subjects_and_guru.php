<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->string('jenjang', 10)->default('umum')->after('subject_code'); // smp, sma, umum
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('jenjang_guru', 20)->nullable()->after('subject_id'); // smp, sma, smp_sma
            $table->foreignId('subject_smp_id')->nullable()->after('jenjang_guru')->constrained('subjects')->nullOnDelete();
            $table->foreignId('subject_sma_id')->nullable()->after('subject_smp_id')->constrained('subjects')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('subject_sma_id');
            $table->dropConstrainedForeignId('subject_smp_id');
            $table->dropColumn('jenjang_guru');
        });
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropColumn('jenjang');
        });
    }
};
