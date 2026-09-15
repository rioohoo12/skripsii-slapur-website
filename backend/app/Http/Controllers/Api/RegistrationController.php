<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\PendaftaranProfile;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class RegistrationController extends Controller
{
    /**
     * POST /api/v1/registrations
     * Handle registration form submission
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // 1. Validasi Request
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'gender' => 'required|in:laki-laki,perempuan',
            'tempat_lahir' => 'required|string|max:255',
            'tanggal_lahir' => 'required|date',
            'alamat' => 'required|string',
            'is_transfer_student' => 'boolean',
            'previous_school_name' => 'nullable|string|max:255',
            'nama_ayah' => 'required|string|max:255',
            'nama_ibu' => 'required|string|max:255',
            'pekerjaan_ayah' => 'required|string|max:255',
            'pekerjaan_ibu' => 'required|string|max:255',
            'no_telp_ortu' => 'required|string|max:50',
            'kelas_yang_didaftar' => 'required|integer|in:7,8,9,10,11,12',
        ]);

        // 2. Sanitasi Input (trim & hapus tag HTML)
        $sanitized = [];
        foreach ($validated as $key => $value) {
            if (is_string($value)) {
                $sanitized[$key] = strip_tags(trim($value));
            } else {
                $sanitized[$key] = $value;
            }
        }

        // 3. Generate Nomor Pendaftaran
        $existingStudent = Student::where('user_id', $user->id)->first();
        $nomorPendaftaran = $existingStudent->nomor_pendaftaran ?? $this->generateNomorPendaftaran();

        // 4. Simpan Student
        $student = Student::updateOrCreate(
            ['user_id' => $user->id],
            [
                'full_name' => $sanitized['full_name'],
                'gender' => $sanitized['gender'],
                'tempat_lahir' => $sanitized['tempat_lahir'],
                'tanggal_lahir' => $sanitized['tanggal_lahir'],
                'alamat' => $sanitized['alamat'],
                'is_transfer_student' => $sanitized['is_transfer_student'] ?? false,
                'previous_school_name' => $sanitized['previous_school_name'] ?? null,
                'nama_ayah' => $sanitized['nama_ayah'],
                'nama_ibu' => $sanitized['nama_ibu'],
                'pekerjaan_ayah' => $sanitized['pekerjaan_ayah'],
                'pekerjaan_ibu' => $sanitized['pekerjaan_ibu'],
                'no_telp_ortu' => $sanitized['no_telp_ortu'],
                'nomor_pendaftaran' => $nomorPendaftaran,
                // 5. Buat status pendaftaran
                'status_pendaftaran' => 'terdaftar',
            ]
        );

        // Update PendaftaranProfile juga untuk sinkronisasi (jika dibutuhkan sistem lain)
        PendaftaranProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'nama_lengkap' => $sanitized['full_name'],
                'no_hp' => $sanitized['no_telp_ortu'],
                'alamat' => $sanitized['alamat'],
                'kelas_yang_didaftar' => $validated['kelas_yang_didaftar'],
            ]
        );

        // 6. Kirim response
        return response()->json([
            'message' => 'Data pendaftaran berhasil disimpan.',
            'nomor_pendaftaran' => $nomorPendaftaran,
            'student' => $student,
        ], 201);
    }

    /**
     * Generate format REG-YYYYMM-XXXX
     */
    private function generateNomorPendaftaran(): string
    {
        $prefix = 'REG-' . date('Ym') . '-';
        
        // Cari nomor terakhir bulan ini
        $lastStudent = Student::where('nomor_pendaftaran', 'LIKE', $prefix . '%')
            ->orderBy('nomor_pendaftaran', 'desc')
            ->first();

        if ($lastStudent && $lastStudent->nomor_pendaftaran) {
            $lastNumber = (int) str_replace($prefix, '', $lastStudent->nomor_pendaftaran);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
