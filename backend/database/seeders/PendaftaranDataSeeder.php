<?php

namespace Database\Seeders;

use App\Models\MataPelajaranTingkat;
use App\Models\PendaftaranKamar;
use Illuminate\Database\Seeder;

class PendaftaranDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedKamar();
        $this->seedMataPelajaran();
    }

    private function seedKamar(): void
    {
        $nomor = ['A1', 'A2', 'A3', 'A4', 'A5', 'B1', 'B2', 'B3', 'B4', 'B5', 'C1', 'C2', 'C3', 'C4', 'C5'];
        foreach ($nomor as $i => $n) {
            PendaftaranKamar::firstOrCreate(
                ['nomor_kamar' => $n],
                ['kapasitas' => 4, 'current_occupancy' => 0]
            );
        }
    }

    private function seedMataPelajaran(): void
    {
        $data = [
            // SMP Kelas 7
            [7, 'Matematika', 1], [7, 'IPA', 2], [7, 'IPS', 3], [7, 'Bahasa Indonesia', 4], [7, 'Bahasa Inggris', 5], [7, 'PKn', 6], [7, 'Agama', 7], [7, 'Seni Budaya', 8], [7, 'PJOK', 9],
            // SMP Kelas 8
            [8, 'Matematika', 1], [8, 'IPA', 2], [8, 'IPS', 3], [8, 'Bahasa Indonesia', 4], [8, 'Bahasa Inggris', 5], [8, 'PKn', 6], [8, 'Agama', 7], [8, 'Seni Budaya', 8], [8, 'PJOK', 9],
            // SMP Kelas 9
            [9, 'Matematika', 1], [9, 'IPA', 2], [9, 'IPS', 3], [9, 'Bahasa Indonesia', 4], [9, 'Bahasa Inggris', 5], [9, 'PKn', 6], [9, 'Agama', 7], [9, 'Seni Budaya', 8], [9, 'PJOK', 9],
            // SMA Kelas 10
            [10, 'Matematika', 1], [10, 'Fisika', 2], [10, 'Kimia', 3], [10, 'Biologi', 4], [10, 'Bahasa Indonesia', 5], [10, 'Bahasa Inggris', 6], [10, 'Sejarah', 7], [10, 'Ekonomi', 8], [10, 'PKn', 9], [10, 'Agama', 10],
            // SMA Kelas 11
            [11, 'Matematika', 1], [11, 'Fisika', 2], [11, 'Kimia', 3], [11, 'Biologi', 4], [11, 'Bahasa Indonesia', 5], [11, 'Bahasa Inggris', 6], [11, 'Sejarah', 7], [11, 'Ekonomi', 8], [11, 'PKn', 9], [11, 'Agama', 10],
            // SMA Kelas 12
            [12, 'Matematika', 1], [12, 'Fisika', 2], [12, 'Kimia', 3], [12, 'Biologi', 4], [12, 'Bahasa Indonesia', 5], [12, 'Bahasa Inggris', 6], [12, 'Sejarah', 7], [12, 'Ekonomi', 8], [12, 'PKn', 9], [12, 'Agama', 10],
        ];
        foreach ($data as $d) {
            MataPelajaranTingkat::firstOrCreate(
                ['tingkat' => $d[0], 'nama_pelajaran' => $d[1]],
                ['urutan' => $d[2]]
            );
        }
    }
}
