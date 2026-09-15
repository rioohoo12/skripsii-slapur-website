<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('nomor_pendaftaran', 50)->nullable()->after('nis');
            $table->string('tempat_lahir', 100)->nullable()->after('full_name');
            $table->date('tanggal_lahir')->nullable()->after('tempat_lahir');
            $table->text('alamat')->nullable()->after('tanggal_lahir');
            $table->string('nama_ayah', 100)->nullable()->after('alamat');
            $table->string('nama_ibu', 100)->nullable()->after('nama_ayah');
            $table->string('pekerjaan_ayah', 100)->nullable()->after('nama_ibu');
            $table->string('pekerjaan_ibu', 100)->nullable()->after('pekerjaan_ayah');
            $table->string('no_telp_ortu', 20)->nullable()->after('pekerjaan_ibu');
            $table->string('status_pendaftaran', 30)->default('pending')->after('no_telp_ortu');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->string('order_id', 100)->nullable()->after('id')->unique();
            $table->string('payment_method', 50)->nullable()->after('payment_status');
            $table->string('transaction_status', 50)->nullable()->after('payment_method');
            $table->timestamp('paid_at')->nullable()->after('verified_at');
            $table->json('midtrans_transaction_data')->nullable()->after('paid_at');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn([
                'nomor_pendaftaran',
                'tempat_lahir',
                'tanggal_lahir',
                'alamat',
                'nama_ayah',
                'nama_ibu',
                'pekerjaan_ayah',
                'pekerjaan_ibu',
                'no_telp_ortu',
                'status_pendaftaran',
            ]);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'order_id',
                'payment_method',
                'transaction_status',
                'paid_at',
                'midtrans_transaction_data',
            ]);
        });
    }
};
