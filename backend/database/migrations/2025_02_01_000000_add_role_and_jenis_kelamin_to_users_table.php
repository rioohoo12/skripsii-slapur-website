<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('role')->default('siswa')->after('email');
            });
        }

        if (!Schema::hasColumn('users', 'jenis_kelamin')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('jenis_kelamin', 20)->nullable()->after('role');
            });
        }
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'role')) {
                $table->dropColumn('role');
            }
            if (Schema::hasColumn('users', 'jenis_kelamin')) {
                $table->dropColumn('jenis_kelamin');
            }
        });
    }
};
