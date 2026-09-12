<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class AcademicSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [
            ['subject_name' => 'Matematika', 'subject_code' => 'MTK', 'jenjang' => 'umum'],
            ['subject_name' => 'Bahasa Indonesia', 'subject_code' => 'BIN', 'jenjang' => 'umum'],
            ['subject_name' => 'Bahasa Inggris', 'subject_code' => 'BIG', 'jenjang' => 'umum'],
            ['subject_name' => 'PKN', 'subject_code' => 'PKN', 'jenjang' => 'umum'],
            ['subject_name' => 'Agama', 'subject_code' => 'AGM', 'jenjang' => 'umum'],
            ['subject_name' => 'IPA', 'subject_code' => 'IPA', 'jenjang' => 'smp'],
            ['subject_name' => 'IPS', 'subject_code' => 'IPS', 'jenjang' => 'smp'],
            ['subject_name' => 'Fisika', 'subject_code' => 'FIS', 'jenjang' => 'sma'],
            ['subject_name' => 'Kimia', 'subject_code' => 'KIM', 'jenjang' => 'sma'],
            ['subject_name' => 'Biologi', 'subject_code' => 'BIO', 'jenjang' => 'sma'],
            ['subject_name' => 'Ekonomi', 'subject_code' => 'EKO', 'jenjang' => 'sma'],
            ['subject_name' => 'Sosiologi', 'subject_code' => 'SOS', 'jenjang' => 'sma'],
            ['subject_name' => 'Sejarah', 'subject_code' => 'SEJ', 'jenjang' => 'sma'],
            ['subject_name' => 'Geografi', 'subject_code' => 'GEO', 'jenjang' => 'sma'],
        ];

        foreach ($subjects as $data) {
            Subject::updateOrCreate(
                ['subject_code' => $data['subject_code']],
                [
                    'subject_name' => $data['subject_name'],
                    'jenjang' => $data['jenjang'],
                ]
            );
        }

        Subject::whereNull('jenjang')->orWhere('jenjang', '')->update(['jenjang' => 'umum']);
    }
}
