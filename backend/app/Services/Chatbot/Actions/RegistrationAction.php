<?php

namespace App\Services\Chatbot\Actions;

use App\Services\Chatbot\Contracts\ActionInterface;
use App\Models\ChatSession;
use App\Services\ApplicantService;
use Illuminate\Support\Facades\Log;

class RegistrationAction implements ActionInterface
{
    protected $applicantService;

    public function __construct(ApplicantService $applicantService)
    {
        $this->applicantService = $applicantService;
    }

    public function execute(ChatSession $session, array $data)
    {
        try {
            // Validation before saving (Double checking the extraction)
            if (empty($data['nama_lengkap']) || empty($data['no_hp'])) {
                return [
                    'status' => 'error',
                    'message_key' => 'registration_missing_data',
                    'data' => []
                ];
            }

            // Create applicant using existing core service
            $applicant = $this->applicantService->createApplicant($data);

            return [
                'status' => 'success',
                'message_key' => 'registration_success',
                'data' => [
                    'nomor_pendaftaran' => $applicant->nomor_pendaftaran,
                    'nama' => $applicant->nama_lengkap,
                    'nominal' => 250000 // Biaya pendaftaran default
                ]
            ];
        } catch (\Exception $e) {
            Log::error('RegistrationAction Error: ' . $e->getMessage());
            return [
                'status' => 'error',
                'message_key' => 'registration_failed',
                'data' => []
            ];
        }
    }
}
