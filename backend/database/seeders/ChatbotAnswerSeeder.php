<?php

namespace Database\Seeders;

use App\Models\ChatbotAnswer;
use Illuminate\Database\Seeder;

class ChatbotAnswerSeeder extends Seeder
{
    public function run(): void
    {
        $answers = [
            [
                'topic' => 'pendaftaran',
                'step_key' => 'salam',
                'trigger_keywords' => ['halo', 'hi', 'hai', 'helo', 'mulai', 'bantu', 'tolong', 'assalamualaikum', 'permisi', '1', '2', '3', '4', '5'],
                'response' => "Halo! Saya Asisten Pendaftaran SLAPUR (Sekolah Lanjutan Advent Purwodadi).\n\n**Anda bisa:**\n1. Cara Daftar\n2. Syarat Pendaftaran\n3. Verifikasi Pendaftaran\n4. Tanya Biaya Pendaftaran dan SPP\n5. Ketik Bantuan\n\nKetik nomor (1-5) atau kata kunci di atas.",
                'next_step_key' => null,
                'order' => 1,
                'is_initial' => true,
            ],
            [
                'topic' => 'pendaftaran',
                'step_key' => 'cara_daftar',
                'trigger_keywords' => ['1', 'cara daftar', 'bagaimana daftar', 'alur pendaftaran', 'proses pendaftaran'],
                'response' => "**Alur Pendaftaran SLAPUR:**\n\n1. **Form Profile** — Isi biodata & kelas yang ingin didaftar (untuk menu Biodata).\n2. **Pembayaran** — Total harga, bayar 60%, verifikasi (status di Clearance Slip).\n3. **Pilih Kamar** — Pilih nomor kamar (kapasitas 4 orang).\n4. **Kurikulum** — Mata pelajaran per kelas (SMP 7-9, SMA 10-12).\n5. **Upload Dokumen** — Akte, KK, Pas foto, Raport (menu Administrasi).\n\nKetik **mulai** untuk isi profile, atau **bantuan** untuk opsi lain.",
                'next_step_key' => null,
                'order' => 2,
                'is_initial' => false,
            ],
            [
                'topic' => 'pendaftaran',
                'step_key' => 'syarat',
                'trigger_keywords' => ['2', 'syarat', 'dokumen', 'persyaratan', 'berkas'],
                'response' => "**Syarat Pendaftaran SLAPUR:**\n\n• Akte Kelahiran\n• Kartu Keluarga (KK)\n• Pas foto 3x4\n• Raport (bagi pindahan)\n\nDokumen diupload di menu **Administrasi > Upload Dokumen**. Ketik **5** atau **bantuan** untuk opsi lain.",
                'next_step_key' => null,
                'order' => 3,
                'is_initial' => false,
            ],
            [
                'topic' => 'pendaftaran',
                'step_key' => 'verifikasi_mulai',
                'trigger_keywords' => ['3', 'verifikasi', 'cek data', 'verifikasi pendaftaran', 'status'],
                'response' => "**Verifikasi Pendaftaran:**\n\nKetik **email** Anda yang terdaftar di SLAPUR. Saya akan cek kelengkapan data Anda.",
                'next_step_key' => 'verifikasi_email',
                'order' => 4,
                'is_initial' => false,
            ],
            [
                'topic' => 'pendaftaran',
                'step_key' => 'verifikasi_email',
                'trigger_keywords' => [],
                'response' => '',
                'next_step_key' => null,
                'order' => 5,
                'is_initial' => false,
            ],
            [
                'topic' => 'pendaftaran',
                'step_key' => 'biaya',
                'trigger_keywords' => ['4', 'biaya', 'spp', 'uang pangkal', 'biaya pendaftaran', 'berapa biaya'],
                'response' => "**Biaya Pendaftaran & SPP:**\n\nTotal dan pembayaran 60% akan ditampilkan setelah Anda mengisi **Form Profile**. Verifikasi pembayaran melalui chatbot; status muncul di **Clearance Slip > Pembayaran Pendaftaran** (Disetujui / Menunggu).\n\nKetik **1** untuk alur daftar atau **bantuan**.",
                'next_step_key' => null,
                'order' => 6,
                'is_initial' => false,
            ],
            [
                'topic' => 'pendaftaran',
                'step_key' => 'bantuan',
                'trigger_keywords' => ['5', 'bantuan', 'help', 'menu'],
                'response' => "**Bantuan:**\n\n1. Cara Daftar\n2. Syarat Pendaftaran\n3. Verifikasi Pendaftaran\n4. Tanya Biaya Pendaftaran dan SPP\n5. Ketik Bantuan\n\nKetik nomor atau kata kunci.",
                'next_step_key' => null,
                'order' => 7,
                'is_initial' => false,
            ],
            [
                'topic' => 'umum',
                'step_key' => 'default',
                'trigger_keywords' => [],
                'response' => "Maaf, saya belum paham. **Anda bisa:**\n1. Cara Daftar\n2. Syarat Pendaftaran\n3. Verifikasi Pendaftaran\n4. Tanya Biaya dan SPP\n5. Bantuan\n\nKetik nomor (1-5) atau kata kunci.",
                'next_step_key' => null,
                'order' => 99,
                'is_initial' => false,
            ],
        ];

        foreach ($answers as $a) {
            ChatbotAnswer::updateOrCreate(
                [
                    'topic' => $a['topic'],
                    'step_key' => $a['step_key'],
                ],
                $a
            );
        }
    }
}
