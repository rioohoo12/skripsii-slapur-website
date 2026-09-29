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
        Schema::table('students', function (Blueprint $table) {
            $table->string('agama')->nullable();
            $table->string('golongan_darah')->nullable();
            $table->string('kewarganegaraan')->default('WNI');
            $table->string('no_telp')->nullable();
            
            $table->string('pendidikan_ayah')->nullable();
            $table->string('penghasilan_ayah')->nullable();
            $table->string('no_telp_ayah')->nullable();
            $table->string('agama_ayah')->nullable();
            $table->string('kewarganegaraan_ayah')->default('WNI');

            $table->string('pendidikan_ibu')->nullable();
            $table->string('penghasilan_ibu')->nullable();
            $table->string('no_telp_ibu')->nullable();
            $table->string('agama_ibu')->nullable();
            $table->string('kewarganegaraan_ibu')->default('WNI');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn([
                'agama', 'golongan_darah', 'kewarganegaraan', 'no_telp',
                'pendidikan_ayah', 'penghasilan_ayah', 'no_telp_ayah', 'agama_ayah', 'kewarganegaraan_ayah',
                'pendidikan_ibu', 'penghasilan_ibu', 'no_telp_ibu', 'agama_ibu', 'kewarganegaraan_ibu'
            ]);
        });
    }
};
