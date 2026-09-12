<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    /**
     * GET /api/subjects — daftar mata pelajaran. Query: ?jenjang=smp|sma (opsional).
     */
    public function index(Request $request): JsonResponse
    {
        $query = Subject::orderBy('jenjang')->orderBy('subject_name');
        $jenjang = $request->query('jenjang');
        if (in_array($jenjang, ['smp', 'sma'], true)) {
            $query->forJenjang($jenjang);
        }
        $subjects = $query->get(['id', 'subject_name', 'subject_code', 'jenjang']);

        return response()->json([
            'subjects' => $subjects,
        ]);
    }
}

