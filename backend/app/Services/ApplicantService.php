<?php

namespace App\Services;

use App\Models\Applicant;
use Illuminate\Support\Str;

class ApplicantService
{
    /**
     * Create a new applicant.
     */
    public function createApplicant(array $data): Applicant
    {
        $data['nomor_pendaftaran'] = $this->generateNomorPendaftaran();
        $data['status'] = 'pending';

        return Applicant::create($data);
    }

    /**
     * Generate a unique registration number.
     */
    private function generateNomorPendaftaran(): string
    {
        // Example format: REG-YYYYMMDD-XXXX
        $date = now()->format('Ymd');
        
        do {
            $randomString = strtoupper(Str::random(4));
            $nomor = "REG-{$date}-{$randomString}";
        } while (Applicant::where('nomor_pendaftaran', $nomor)->exists());

        return $nomor;
    }
}
