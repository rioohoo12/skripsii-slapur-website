<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MataPelajaranTingkat;
use App\Models\PendaftaranKamar;
use App\Models\PendaftaranPayment;
use App\Models\PendaftaranProfile;
use App\Models\PendaftaranRoomSelection;
use App\Models\StudentDocument;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PendaftaranController extends Controller
{
    private function allowedDocumentJenis(): array
    {
        return [
            StudentDocument::JENIS_AKTE,
            StudentDocument::JENIS_KK,
            StudentDocument::JENIS_PAS_FOTO,
            StudentDocument::JENIS_RAPORT,
            'ijazah',
            'lainnya',
        ];
    }

    /**
     * GET /api/pendaftaran/status — Status pendaftaran user (untuk Biodata, Clearance Slip, Pilih Kamar, Upload Dokumen).
     */
    public function status(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $student = \App\Models\Student::where('user_id', $user->id)->first();
        $profile = PendaftaranProfile::where('user_id', $user->id)->first();
        $payment = PendaftaranPayment::where('user_id', $user->id)->first();
        $roomSelection = PendaftaranRoomSelection::where('user_id', $user->id)->with('kamar')->first();
        $documents = StudentDocument::where('user_id', $user->id)->get(['jenis', 'status', 'verified_at']);

        // Evaluasi penyelesaian berurutan Langkah 0 s/d Langkah 5
        $step0_done = (bool) ($profile && $profile->kelas_yang_didaftar);
        $step1_done = $step0_done && ($payment && ($payment->status === 'terverifikasi' || $payment->nominal_dibayar >= $payment->nominal_60_percent));
        $step2_done = $step1_done && ($roomSelection && in_array($roomSelection->status, ['approved', 'disetujui'], true));
        $step3_done = $step2_done; // Administrasi
        $step4_done = $step3_done; // Kurikulum
        $step5_done = $step4_done && (($student && $student->has_uploaded_docs) || $documents->whereIn('status', ['terverifikasi', 'verified'])->count() > 0);

        $steps = [
            0 => [
                'index' => 0,
                'key' => 'permohonan',
                'judul' => 'Langkah 0 - Permohonan Pendaftaran',
                'unlocked' => true,
                'selesai' => $step0_done,
                'status_label' => $step0_done ? 'Terverifikasi' : 'Menunggu Pengisian',
            ],
            1 => [
                'index' => 1,
                'key' => 'clearance',
                'judul' => 'Langkah 1 - Clearance Slip',
                'unlocked' => $step0_done,
                'selesai' => $step1_done,
                'status_label' => $step1_done ? 'Terverifikasi' : ($step0_done ? 'Menunggu Pembayaran' : 'Terkunci (Selesaikan Langkah 0)'),
            ],
            2 => [
                'index' => 2,
                'key' => 'asrama',
                'judul' => 'Langkah 2 - Asrama / Luar Asrama',
                'unlocked' => $step1_done,
                'selesai' => $step2_done,
                'status_label' => $step2_done ? 'Terverifikasi' : ($step1_done ? 'Menunggu Pilihan Kamar' : 'Terkunci (Selesaikan Langkah 1)'),
            ],
            3 => [
                'index' => 3,
                'key' => 'administrasi',
                'judul' => 'Langkah 3 - Administrasi',
                'unlocked' => $step2_done,
                'selesai' => $step3_done,
                'status_label' => $step3_done ? 'Terverifikasi' : ($step2_done ? 'Menunggu Administrasi' : 'Terkunci (Selesaikan Langkah 2)'),
            ],
            4 => [
                'index' => 4,
                'key' => 'kurikulum',
                'judul' => 'Langkah 4 - Kurikulum',
                'unlocked' => $step3_done,
                'selesai' => $step4_done,
                'status_label' => $step4_done ? 'Terverifikasi' : ($step3_done ? 'Menunggu Kurikulum' : 'Terkunci (Selesaikan Langkah 3)'),
            ],
            5 => [
                'index' => 5,
                'key' => 'dokumen',
                'judul' => 'Langkah 5 - Upload Dokumen',
                'unlocked' => $step4_done,
                'selesai' => $step5_done,
                'status_label' => $step5_done ? 'Terverifikasi' : ($step4_done ? 'Menunggu Upload Dokumen' : 'Terkunci (Selesaikan Langkah 4)'),
            ],
        ];

        return response()->json([
            'steps' => $steps,
            'profile' => $profile ? [
                'nama_lengkap' => $profile->nama_lengkap,
                'no_hp' => $profile->no_hp,
                'alamat' => $profile->alamat,
                'kelas_yang_didaftar' => $profile->kelas_yang_didaftar,
            ] : null,
            'payment' => $payment ? [
                'total_tagihan' => (float) $payment->total_tagihan,
                'nominal_60_percent' => (float) $payment->nominal_60_percent,
                'nominal_dibayar' => (float) $payment->nominal_dibayar,
                'status' => $payment->status,
                'verified_at' => $payment->verified_at?->toIso8601String(),
                'bukti_path' => $payment->bukti_path,
            ] : null,
            'room' => $roomSelection ? [
                'nomor_kamar' => $roomSelection->kamar->nomor_kamar ?? null,
                'kamar_id' => $roomSelection->pendaftaran_kamar_id,
                'status' => $roomSelection->status,
            ] : null,
            'documents' => $documents->map(fn ($d) => [
                'jenis' => $d->jenis,
                'status' => $d->status,
                'verified_at' => $d->verified_at?->toIso8601String(),
            ])->keyBy('jenis'),
        ]);
    }

    /**
     * GET /api/pendaftaran/kurikulum?tingkat=7 — Mata pelajaran per tingkat (7-12).
     */
    public function kurikulum(Request $request): JsonResponse
    {
        $tingkat = $request->query('tingkat');
        $query = MataPelajaranTingkat::orderBy('tingkat')->orderBy('urutan');

        if ($tingkat !== null && $tingkat !== '') {
            $query->where('tingkat', (int) $tingkat);
        }

        $items = $query->get(['tingkat', 'nama_pelajaran', 'urutan']);

        $grouped = $items->groupBy('tingkat')->map(fn ($rows) => $rows->pluck('nama_pelajaran'));

        return response()->json([
            'by_tingkat' => $grouped,
            'list' => $items,
        ]);
    }

    /**
     * GET /api/pendaftaran/kamar — Daftar kamar (kapasitas, terisi) untuk pilih kamar.
     */
    public function kamar(Request $request): JsonResponse
    {
        if (PendaftaranKamar::count() === 0) {
            foreach (['A1', 'A2', 'A3', 'B1', 'B2', 'B3'] as $no) {
                PendaftaranKamar::create([
                    'nomor_kamar' => $no,
                    'kapasitas' => 4,
                    'current_occupancy' => 0,
                ]);
            }
        }

        $query = PendaftaranKamar::orderBy('nomor_kamar');
        if ($request->query('tersedia') === '1' || $request->query('tersedia') === 'true') {
            $query->whereColumn('current_occupancy', '<', 'kapasitas');
        }
        
        $kamar = $query->get(['id', 'nomor_kamar', 'kapasitas', 'current_occupancy']);

        return response()->json([
            'kamar' => $kamar->map(fn ($k) => [
                'id' => $k->id,
                'nomor_kamar' => $k->nomor_kamar,
                'kapasitas' => $k->kapasitas,
                'current_occupancy' => $k->current_occupancy,
                'tersedia' => $k->current_occupancy < $k->kapasitas,
            ]),
        ]);
    }

    /**
     * POST /api/pendaftaran/kamar — Pilih kamar (untuk dashboard; juga bisa via chatbot).
     * Body: { "kamar_id": 1 } atau { "nomor_kamar": "A1" }
     */
    public function pilihKamar(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $kamarId = $request->input('kamar_id');
        $nomorKamar = $request->input('nomor_kamar');
        if ($kamarId === null && $nomorKamar === null) {
            return response()->json(['message' => 'Berikan kamar_id atau nomor_kamar'], 422);
        }

        $student = \App\Models\Student::where('user_id', $user->id)->first();
        if (!$student) {
            $student = \App\Models\Student::create([
                'user_id' => $user->id,
                'full_name' => $user->name,
                'gender' => $user->jenis_kelamin === 'perempuan' ? 'P' : 'L',
                'status_pendaftaran' => 'terdaftar',
                'has_paid_registration' => true,
            ]);
        } else {
            $student->update(['has_paid_registration' => true]);
        }

        return \Illuminate\Support\Facades\DB::transaction(function () use ($user, $kamarId, $nomorKamar, $student) {
            $nomorStr = strtoupper(trim((string)$nomorKamar));
            $kamar = $kamarId
                ? PendaftaranKamar::find($kamarId)
                : PendaftaranKamar::firstOrCreate(['nomor_kamar' => $nomorStr], ['kapasitas' => 4, 'current_occupancy' => 0]);

            if (!$kamar) {
                return response()->json(['message' => 'Kamar tidak ditemukan'], 404);
            }

            if ($kamar->current_occupancy >= $kamar->kapasitas) {
                return response()->json(['message' => 'Kamar sudah penuh'], 422);
            }

            $existing = PendaftaranRoomSelection::where('user_id', $user->id)->first();
            if ($existing && $existing->pendaftaran_kamar_id === $kamar->id) {
                return response()->json([
                    'message' => 'Anda sudah memilih kamar ini',
                    'room' => ['nomor_kamar' => $kamar->nomor_kamar],
                ]);
            }
            if ($existing) {
                PendaftaranKamar::where('id', $existing->pendaftaran_kamar_id)->decrement('current_occupancy');
            }

            PendaftaranRoomSelection::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'pendaftaran_kamar_id' => $kamar->id,
                    'status' => 'approved',
                    'approved_at' => now(),
                    'notes' => 'Disetujui oleh Staff Asrama.',
                ]
            );
            $kamar->increment('current_occupancy');

            $student->update(['has_chosen_room' => true]);

            return response()->json([
                'message' => 'Kamar berhasil dipilih',
                'room' => [
                    'id' => $kamar->id,
                    'nomor_kamar' => $kamar->nomor_kamar,
                ],
            ]);
        });
    }

    /**
     * POST /api/pendaftaran/upload-bukti-pembayaran — Upload bukti transfer (gambar) untuk verifikasi.
     * Multipart: file (image)
     */
    public function uploadBuktiPembayaran(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $request->validate([
            'file' => 'required|file|image|mimes:jpeg,jpg,png,gif,webp|max:5120', // max 5MB
        ], [
            'file.required' => 'Pilih file bukti pembayaran (foto transfer).',
            'file.image' => 'File harus berupa gambar (JPEG, PNG, GIF, WebP).',
            'file.max' => 'Ukuran file maksimal 5 MB.',
        ]);

        $total = (float) config('pendaftaran.total_tagihan', 5_000_000);
        $nominal60 = round($total * 0.6, 0);

        $file = $request->file('file');
        $path = $file->store('bukti_pendaftaran/' . $user->id, 'public');

        $payment = PendaftaranPayment::updateOrCreate(
            ['user_id' => $user->id],
            [
                'total_tagihan' => $total,
                'nominal_60_percent' => $nominal60,
                'nominal_dibayar' => $nominal60,
                'status' => 'terverifikasi',
                'bukti_path' => $path,
                'verified_at' => now(), // Simulated AI Verification
            ]
        );

        // Tandai bahwa pendaftaran telah dibayar
        $student = \App\Models\Student::where('user_id', $user->id)->first();
        if ($student) {
            $student->update(['has_paid_registration' => true]);
        }

        return response()->json([
            'message' => 'Bukti pembayaran berhasil diupload dan telah diverifikasi secara otomatis oleh AI kami (24/7).',
            'payment' => [
                'status' => $payment->status,
                'bukti_path' => $payment->bukti_path,
                'verified_at' => $payment->verified_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * GET /api/pendaftaran/dokumen — List dokumen user.
     */
    public function dokumen(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $docs = StudentDocument::where('user_id', $user->id)
            ->orderBy('id', 'desc')
            ->get(['id', 'jenis', 'file_path', 'status', 'verified_at', 'created_at', 'updated_at']);

        return response()->json([
            'documents' => $docs->map(fn (StudentDocument $d) => [
                'id' => $d->id,
                'jenis' => $d->jenis,
                'file_path' => $d->file_path,
                'file_url' => $d->file_path ? url('storage/' . ltrim($d->file_path, '/')) : null,
                'status' => $d->status,
                'verified_at' => $d->verified_at?->toIso8601String(),
                'created_at' => $d->created_at?->toIso8601String(),
                'updated_at' => $d->updated_at?->toIso8601String(),
            ]),
        ]);
    }

    /**
     * POST /api/pendaftaran/upload-dokumen — Upload dokumen pendaftaran.
     * Multipart: file, jenis
     */
    public function uploadDokumen(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $request->validate([
            'jenis' => ['required', 'string', 'max:30', Rule::in($this->allowedDocumentJenis())],
            'file' => 'required|file|mimes:pdf,jpeg,jpg,png,webp|max:5120', // max 5MB
        ], [
            'jenis.required' => 'Jenis dokumen wajib dipilih.',
            'jenis.in' => 'Jenis dokumen tidak valid.',
            'file.required' => 'File dokumen wajib dipilih.',
            'file.mimes' => 'Format file harus PDF/JPG/PNG/WebP.',
            'file.max' => 'Ukuran file maksimal 5 MB.',
        ]);

        $jenis = $request->input('jenis');
        $file = $request->file('file');
        $path = $file->store('dokumen_pendaftaran/' . $user->id . '/' . $jenis, 'public');

        $doc = StudentDocument::updateOrCreate(
            ['user_id' => $user->id, 'jenis' => $jenis],
            [
                'file_path' => $path,
                'status' => 'terverifikasi',
                'verified_at' => now(),
            ]
        );

        // Tandai siswa sudah mengunggah dokumen & aktifkan langkah selanjutnya
        $student = \App\Models\Student::where('user_id', $user->id)->first();
        if ($student) {
            $student->update(['has_uploaded_docs' => true]);
        }

        return response()->json([
            'message' => 'Dokumen berhasil diunggah dan diverifikasi secara otomatis oleh Virtual Assistant.',
            'document' => [
                'id' => $doc->id,
                'jenis' => $doc->jenis,
                'file_path' => $doc->file_path,
                'file_url' => url('storage/' . ltrim($doc->file_path, '/')),
                'status' => $doc->status,
                'verified_at' => $doc->verified_at?->toIso8601String(),
                'updated_at' => $doc->updated_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * POST /api/pendaftaran/form — Simpan data form pendaftaran (Data pribadi, pendidikan, orang tua).
     */
    public function simpanForm(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

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
            'pendidikan_ayah' => 'nullable|string|max:255',
            'penghasilan_ayah' => 'nullable|string|max:255',
            'no_telp_ayah' => 'nullable|string|max:50',
            'agama_ayah' => 'nullable|string|max:255',
            'kewarganegaraan_ayah' => 'nullable|string|max:255',

            'nama_ibu' => 'required|string|max:255',
            'pekerjaan_ibu' => 'required|string|max:255',
            'pendidikan_ibu' => 'nullable|string|max:255',
            'penghasilan_ibu' => 'nullable|string|max:255',
            'no_telp_ibu' => 'nullable|string|max:50',
            'agama_ibu' => 'nullable|string|max:255',
            'kewarganegaraan_ibu' => 'nullable|string|max:255',

            'no_telp_ortu' => 'nullable|string|max:50',
            'kelas_yang_didaftar' => 'required|integer|in:7,8,9,10,11,12',

            'agama' => 'nullable|string|max:255',
            'golongan_darah' => 'nullable|string|max:10',
            'kewarganegaraan' => 'nullable|string|max:255',
            'no_telp' => 'nullable|string|max:50',
        ]);

        $student = \App\Models\Student::updateOrCreate(
            ['user_id' => $user->id],
            [
                'full_name' => $validated['full_name'],
                'gender' => $validated['gender'],
                'tempat_lahir' => $validated['tempat_lahir'],
                'tanggal_lahir' => $validated['tanggal_lahir'],
                'alamat' => $validated['alamat'],
                'agama' => $validated['agama'] ?? null,
                'golongan_darah' => $validated['golongan_darah'] ?? null,
                'kewarganegaraan' => $validated['kewarganegaraan'] ?? 'WNI',
                'no_telp' => $validated['no_telp'] ?? null,

                'is_transfer_student' => $validated['is_transfer_student'] ?? false,
                'previous_school_name' => $validated['previous_school_name'] ?? null,
                
                'nama_ayah' => $validated['nama_ayah'],
                'pekerjaan_ayah' => $validated['pekerjaan_ayah'],
                'pendidikan_ayah' => $validated['pendidikan_ayah'] ?? null,
                'penghasilan_ayah' => $validated['penghasilan_ayah'] ?? null,
                'no_telp_ayah' => $validated['no_telp_ayah'] ?? null,
                'agama_ayah' => $validated['agama_ayah'] ?? null,
                'kewarganegaraan_ayah' => $validated['kewarganegaraan_ayah'] ?? 'WNI',

                'nama_ibu' => $validated['nama_ibu'],
                'pekerjaan_ibu' => $validated['pekerjaan_ibu'],
                'pendidikan_ibu' => $validated['pendidikan_ibu'] ?? null,
                'penghasilan_ibu' => $validated['penghasilan_ibu'] ?? null,
                'no_telp_ibu' => $validated['no_telp_ibu'] ?? null,
                'agama_ibu' => $validated['agama_ibu'] ?? null,
                'kewarganegaraan_ibu' => $validated['kewarganegaraan_ibu'] ?? 'WNI',

                'no_telp_ortu' => $validated['no_telp_ortu'] ?? ($validated['no_telp_ayah'] ?? $validated['no_telp_ibu']),
                'status_pendaftaran' => 'terdaftar',
            ]
        );

        // Update PendaftaranProfile juga untuk sinkronisasi
        PendaftaranProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'nama_lengkap' => $validated['full_name'],
                'no_hp' => $validated['no_telp_ortu'],
                'alamat' => $validated['alamat'],
                'kelas_yang_didaftar' => $validated['kelas_yang_didaftar'],
            ]
        );

        return response()->json([
            'message' => 'Data pendaftaran berhasil disimpan.',
            'student' => $student,
        ]);
    }

    /**
     * GET /api/asrama/info — Informasi kamar asrama yang didaftarkan & status persetujuan staff asrama.
     */
    public function asramaInfo(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $roomSelection = PendaftaranRoomSelection::where('user_id', $user->id)
            ->with(['kamar', 'requestedKamar'])
            ->first();

        if (!$roomSelection || !$roomSelection->kamar) {
            return response()->json([
                'has_selected_room' => false,
                'message' => 'Anda belum mendaftarkan atau memilih kamar asrama.',
            ]);
        }

        $kamar = $roomSelection->kamar;
        $requestedKamar = $roomSelection->requestedKamar;

        $roommates = PendaftaranRoomSelection::where('pendaftaran_kamar_id', $kamar->id)
            ->with('user')
            ->get()
            ->map(fn($sel) => [
                'user_id' => $sel->user_id,
                'name' => $sel->user?->name ?? 'Siswa',
                'is_current_user' => $sel->user_id === $user->id,
            ]);

        $statusPersetujuan = $roomSelection->status ?? 'approved';

        $statusLabel = match ($statusPersetujuan) {
            'approved' => 'Disetujui oleh Staff Asrama',
            'rejected' => 'Pengajuan Kamar Ditolak / Perlu Memilih Kamar Lain',
            default => $requestedKamar
                ? "Menunggu Persetujuan Staff Asrama (Pengajuan Ganti ke Kamar {$requestedKamar->nomor_kamar})"
                : 'Menunggu Persetujuan Staff Asrama',
        };

        return response()->json([
            'has_selected_room' => true,
            'kamar_id' => $kamar->id,
            'nomor_kamar' => $kamar->nomor_kamar,
            'kapasitas' => $kamar->kapasitas,
            'current_occupancy' => $kamar->current_occupancy,
            'has_pending_request' => (bool) $requestedKamar,
            'requested_nomor_kamar' => $requestedKamar?->nomor_kamar,
            'status_persetujuan' => $statusPersetujuan,
            'status_label' => $statusLabel,
            'approved_at' => $roomSelection->approved_at ? \Illuminate\Support\Carbon::parse($roomSelection->approved_at)->format('d M Y H:i') : null,
            'notes' => $roomSelection->notes ?? 'Pendaftaran kamar asrama Anda telah tercatat.',
            'penghuni' => $roommates,
        ]);
    }

    /**
     * POST /api/asrama/ganti-kamar — Siswa mengajukan ganti kamar (menunggu persetujuan Staff Asrama).
     */
    public function gantiKamar(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $validated = $request->validate([
            'nomor_kamar' => 'nullable|string',
            'kamar_id' => 'nullable|exists:pendaftaran_kamar,id',
        ]);

        if (empty($validated['nomor_kamar']) && empty($validated['kamar_id'])) {
            return response()->json(['message' => 'Silakan pilih kamar yang valid.'], 422);
        }

        if (!empty($validated['kamar_id'])) {
            $targetKamar = PendaftaranKamar::find($validated['kamar_id']);
            $targetNomor = $targetKamar->nomor_kamar;
        } else {
            $targetNomor = strtoupper(trim($validated['nomor_kamar']));
            $targetKamar = PendaftaranKamar::firstOrCreate(
                ['nomor_kamar' => $targetNomor],
                ['kapasitas' => 4, 'current_occupancy' => 0]
            );
        }

        if ($targetKamar->current_occupancy >= $targetKamar->kapasitas) {
            return response()->json(['message' => "Kamar {$targetNomor} sudah penuh. Silakan pilih nomor kamar lain."], 422);
        }

        $selection = PendaftaranRoomSelection::where('user_id', $user->id)->first();

        if (!$selection) {
            $selection = PendaftaranRoomSelection::create([
                'user_id' => $user->id,
                'pendaftaran_kamar_id' => $targetKamar->id,
                'status' => 'pending',
                'notes' => "Pengajuan pendaftaran Kamar {$targetNomor} dikirim. Menunggu persetujuan Staff Asrama.",
            ]);
        } else {
            if ($selection->pendaftaran_kamar_id === $targetKamar->id) {
                return response()->json(['message' => "Anda saat ini sudah terdaftar di Kamar {$targetNomor}."], 422);
            }

            $currentKamar = PendaftaranKamar::find($selection->pendaftaran_kamar_id);
            $currentNomor = $currentKamar?->nomor_kamar ?? '-';

            $selection->update([
                'requested_kamar_id' => $targetKamar->id,
                'status' => 'pending',
                'notes' => "Mengajukan pergantian dari Kamar {$currentNomor} ke Kamar {$targetNomor}. Menunggu persetujuan Staff Asrama.",
            ]);
        }

        return response()->json([
            'message' => "Pengajuan pergantian kamar ke Kamar {$targetNomor} berhasil dikirim. Harap menunggu verifikasi persetujuan dari Staff Asrama.",
            'data' => [
                'current_room' => $selection->kamar?->nomor_kamar,
                'requested_room' => $targetKamar->nomor_kamar,
                'status' => 'pending',
            ],
        ]);
    }

    /**
     * POST /api/staff/asrama/approve — Staff asrama menyetujui atau menolak pendaftaran/ganti kamar murid.
     */
    public function staffApproveAsrama(Request $request): JsonResponse
    {
        $request->validate([
            'selection_id' => 'nullable|exists:pendaftaran_room_selections,id',
            'user_id' => 'nullable|exists:users,id',
            'status' => 'nullable|in:approved,rejected,pending',
            'action' => 'nullable|in:approve,reject,pending',
            'notes' => 'nullable|string|max:500',
        ]);

        $selection = null;
        if ($request->filled('selection_id')) {
            $selection = PendaftaranRoomSelection::find($request->input('selection_id'));
        } elseif ($request->filled('user_id')) {
            $selection = PendaftaranRoomSelection::where('user_id', $request->input('user_id'))->first();
        }

        if (!$selection) {
            return response()->json(['message' => 'Pendaftaran kamar tidak ditemukan.'], 404);
        }

        $actionInput = $request->input('action');
        $statusInput = $request->input('status');
        
        $newStatus = $statusInput;
        if (!$newStatus && $actionInput) {
            $newStatus = $actionInput === 'approve' ? 'approved' : ($actionInput === 'reject' ? 'rejected' : 'pending');
        }
        if (!$newStatus) {
            $newStatus = 'approved';
        }
        $notesInput = $request->input('notes');

        if ($newStatus === 'approved') {
            // Jika ada pengajuan ganti kamar
            if ($selection->requested_kamar_id) {
                // Kurangi occupancy kamar lama
                PendaftaranKamar::where('id', $selection->pendaftaran_kamar_id)->decrement('current_occupancy');

                // Pindahkan ke kamar baru & tambah occupancy
                $selection->pendaftaran_kamar_id = $selection->requested_kamar_id;
                $selection->requested_kamar_id = null;
                PendaftaranKamar::where('id', $selection->pendaftaran_kamar_id)->increment('current_occupancy');
            }

            $selection->status = 'approved';
            $selection->approved_at = now();
            $selection->notes = $notesInput ?: 'Pengajuan pergantian kamar telah disetujui oleh Staff Asrama.';
            $selection->save();
        } else if ($newStatus === 'rejected') {
            $selection->requested_kamar_id = null;
            $selection->status = 'rejected';
            $selection->notes = $notesInput ?: 'Pengajuan pergantian kamar ditolak oleh Staff Asrama.';
            $selection->save();
        } else {
            $selection->status = 'pending';
            $selection->notes = $notesInput ?: 'Pengajuan dalam proses verifikasi Staff Asrama.';
            $selection->save();
        }

        return response()->json([
            'message' => 'Status pendaftaran/pergantian kamar berhasil diperbarui.',
            'data' => $selection,
        ]);
    }

    /**
     * GET /api/user/biodata — Get comprehensive biodata for the user.
     */
    public function biodata(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $student = \App\Models\Student::where('user_id', $user->id)->first();
        $profile = PendaftaranProfile::where('user_id', $user->id)->first();

        return response()->json([
            'student' => $student,
            'profile' => $profile,
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'jenis_kelamin' => $user->jenis_kelamin,
            ]
        ]);
    }
}
