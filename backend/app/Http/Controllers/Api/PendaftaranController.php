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

        $profile = PendaftaranProfile::where('user_id', $user->id)->first();
        $payment = PendaftaranPayment::where('user_id', $user->id)->first();
        $roomSelection = PendaftaranRoomSelection::where('user_id', $user->id)->with('kamar')->first();
        $documents = StudentDocument::where('user_id', $user->id)->get(['jenis', 'status', 'verified_at']);

        return response()->json([
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
                'status' => $payment->status, // menunggu | terverifikasi
                'verified_at' => $payment->verified_at?->toIso8601String(),
                'bukti_path' => $payment->bukti_path,
            ] : null,
            'room' => $roomSelection ? [
                'nomor_kamar' => $roomSelection->kamar->nomor_kamar ?? null,
                'kamar_id' => $roomSelection->pendaftaran_kamar_id,
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
        $kamar = PendaftaranKamar::orderBy('nomor_kamar')->get(['id', 'nomor_kamar', 'kapasitas', 'current_occupancy']);

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

        $kamar = $kamarId
            ? PendaftaranKamar::find($kamarId)
            : PendaftaranKamar::where('nomor_kamar', trim($nomorKamar))->first();

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
            ['pendaftaran_kamar_id' => $kamar->id]
        );
        $kamar->increment('current_occupancy');

        return response()->json([
            'message' => 'Kamar berhasil dipilih',
            'room' => [
                'id' => $kamar->id,
                'nomor_kamar' => $kamar->nomor_kamar,
            ],
        ]);
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
                'status' => 'menunggu',
                'bukti_path' => $path,
            ]
        );

        return response()->json([
            'message' => 'Bukti pembayaran berhasil diupload. Menunggu verifikasi admin.',
            'payment' => [
                'status' => $payment->status,
                'bukti_path' => $payment->bukti_path,
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
                'status' => 'pending',
                'verified_at' => null,
            ]
        );

        return response()->json([
            'message' => 'Dokumen berhasil diupload. Menunggu verifikasi staff.',
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
}
