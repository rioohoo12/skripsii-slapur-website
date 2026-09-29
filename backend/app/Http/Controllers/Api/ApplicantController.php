<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreApplicantRequest;
use App\Services\ApplicantService;
use Illuminate\Http\JsonResponse;

class ApplicantController extends Controller
{
    protected ApplicantService $applicantService;

    public function __construct(ApplicantService $applicantService)
    {
        $this->applicantService = $applicantService;
    }

    public function store(StoreApplicantRequest $request): JsonResponse
    {
        $applicant = $this->applicantService->createApplicant($request->validated());

        return response()->json([
            'message' => 'Pendaftaran berhasil dibuat.',
            'data' => $applicant
        ], 201);
    }
}
