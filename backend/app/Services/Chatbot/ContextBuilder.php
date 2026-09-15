<?php

namespace App\Services\Chatbot;

use App\Models\ChatbotSession;
use App\Models\PendaftaranKamar;
use App\Models\PendaftaranPayment;
use App\Models\PendaftaranProfile;
use App\Models\PendaftaranRoomSelection;
use App\Models\Student;
use App\Models\StudentDocument;
use App\Models\Subject;
use App\Models\User;

class ContextBuilder
{
    /**
     * Membangun array konteks komprehensif untuk pengolahan jawaban dan LLM.
     */
    public function build(ChatbotSession $session, ?User $user = null): array
    {
        $context = [
            'session_id' => $session->session_id,
            'current_step' => $session->current_step_key,
            'user' => null,
            'student' => null,
            'profile' => null,
            'payment' => null,
            'room' => null,
            'documents' => [],
            'dining_number' => null,
            'subjects' => [],
        ];

        if (!$user && $session->user_id) {
            $user = User::find($session->user_id);
        }

        if ($user) {
            $context['user'] = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'jenis_kelamin' => $user->jenis_kelamin,
            ];

            $student = Student::where('user_id', $user->id)->first();
            if ($student) {
                $context['student'] = [
                    'id' => $student->id,
                    'full_name' => $student->full_name,
                    'nomor_pendaftaran' => $student->nomor_pendaftaran,
                    'status_pendaftaran' => $student->status_pendaftaran,
                    'has_uploaded_docs' => $student->has_uploaded_docs,
                    'dining_number' => $student->getDiningNumberFormatted(),
                ];
                $context['dining_number'] = $student->getDiningNumberFormatted();
            }

            $profile = PendaftaranProfile::where('user_id', $user->id)->first();
            if ($profile) {
                $context['profile'] = [
                    'nama_lengkap' => $profile->nama_lengkap,
                    'no_hp' => $profile->no_hp,
                    'alamat' => $profile->alamat,
                    'kelas_yang_didaftar' => $profile->kelas_yang_didaftar,
                ];

                if ($profile->kelas_yang_didaftar) {
                    $jenjang = $profile->kelas_yang_didaftar <= 9 ? 'smp' : 'sma';
                    $context['subjects'] = Subject::forJenjang($jenjang)->pluck('subject_name')->toArray();
                }
            }

            $payment = PendaftaranPayment::where('user_id', $user->id)->first();
            if ($payment) {
                $context['payment'] = [
                    'status' => $payment->status,
                    'nominal_60_percent' => $payment->nominal_60_percent,
                    'total_tagihan' => $payment->total_tagihan,
                    'bukti_path' => (bool) $payment->bukti_path,
                ];
            }

            $roomSelection = PendaftaranRoomSelection::where('user_id', $user->id)->first();
            if ($roomSelection) {
                $kamar = PendaftaranKamar::find($roomSelection->pendaftaran_kamar_id);
                if ($kamar) {
                    $context['room'] = [
                        'nomor_kamar' => $kamar->nomor_kamar,
                        'lokasi' => $kamar->lokasi,
                    ];
                }
            }

            $context['documents'] = StudentDocument::where('user_id', $user->id)
                ->get(['jenis', 'status'])
                ->toArray();
        }

        return $context;
    }
}
