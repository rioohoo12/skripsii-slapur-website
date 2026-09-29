<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PendaftaranKamar;
use App\Models\PendaftaranRoomSelection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AsramaStaffController extends Controller
{
    /**
     * GET /api/staff/asrama/dashboard
     * Mengambil statistik untuk dashboard Asrama (US-03)
     */
    public function dashboard(Request $request): JsonResponse
    {
        $kamar = PendaftaranKamar::all();
        $totalKamar = $kamar->count();
        $totalPenghuni = $kamar->sum('current_occupancy');
        
        $kamarKosong = $kamar->filter(function ($k) {
            return $k->current_occupancy < $k->kapasitas;
        })->count();

        $permintaanPindah = PendaftaranRoomSelection::whereNotNull('requested_kamar_id')
            ->where('status', 'pending')
            ->count();
        
        $pendaftarBaru = PendaftaranRoomSelection::whereNull('requested_kamar_id')
            ->where('status', 'pending')
            ->count();

        // Bisa ditambahkan rekap gedung atau tipe jika tabel PendaftaranKamar menyimpan info gedung, 
        // tapi saat ini hanya berdasar nomor_kamar. (Misal A = Gedung A)
        $kapasitasGedung = [
            [
                'gedung' => 'Gedung A (Krisan)',
                'occupancy' => $this->calculateOccupancyPercentage($kamar, 'A'),
            ],
            [
                'gedung' => 'Gedung B (Melati)',
                'occupancy' => $this->calculateOccupancyPercentage($kamar, 'B'),
            ],
        ];

        return response()->json([
            'total_kamar' => $totalKamar,
            'total_penghuni' => $totalPenghuni,
            'kamar_kosong' => $kamarKosong,
            'permintaan_pindah' => $permintaanPindah,
            'pendaftar_baru' => $pendaftarBaru,
            'kapasitas_gedung' => $kapasitasGedung,
        ]);
    }

    private function calculateOccupancyPercentage($kamar, $prefix)
    {
        $filtered = $kamar->filter(fn($k) => str_starts_with(strtoupper($k->nomor_kamar), $prefix));
        if ($filtered->isEmpty()) return 0;

        $cap = $filtered->sum('kapasitas');
        $occ = $filtered->sum('current_occupancy');
        return $cap > 0 ? round(($occ / $cap) * 100) : 0;
    }

    /**
     * GET /api/staff/asrama/kamar
     * List Master Kamar (US-04, US-05, US-08)
     */
    public function listKamar(Request $request): JsonResponse
    {
        $kamar = PendaftaranKamar::orderBy('nomor_kamar')->get();
        return response()->json(['data' => $kamar]);
    }

    /**
     * POST /api/staff/asrama/kamar
     * Tambah Kamar Baru (US-04, US-08)
     */
    public function storeKamar(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nomor_kamar' => 'required|string|unique:pendaftaran_kamar,nomor_kamar|max:20',
            'kapasitas' => 'required|integer|min:1',
            'status_kondisi' => 'nullable|string|max:30',
            'catatan_fasilitas' => 'nullable|string',
        ]);

        $kamar = PendaftaranKamar::create([
            'nomor_kamar' => strtoupper(trim($validated['nomor_kamar'])),
            'kapasitas' => $validated['kapasitas'],
            'status_kondisi' => $validated['status_kondisi'] ?? 'Baik',
            'catatan_fasilitas' => $validated['catatan_fasilitas'] ?? null,
            'current_occupancy' => 0,
        ]);

        return response()->json(['message' => 'Kamar berhasil ditambahkan', 'data' => $kamar]);
    }

    /**
     * PUT /api/staff/asrama/kamar/{id}
     * Update Kamar (US-04, US-08)
     */
    public function updateKamar(Request $request, $id): JsonResponse
    {
        $kamar = PendaftaranKamar::find($id);
        if (!$kamar) {
            return response()->json(['message' => 'Kamar tidak ditemukan'], 404);
        }

        $validated = $request->validate([
            'nomor_kamar' => 'required|string|max:20|unique:pendaftaran_kamar,nomor_kamar,' . $kamar->id,
            'kapasitas' => 'required|integer|min:' . $kamar->current_occupancy, // Tidak boleh lebih kecil dari penghuni saat ini
            'status_kondisi' => 'nullable|string|max:30',
            'catatan_fasilitas' => 'nullable|string',
        ]);

        $kamar->update([
            'nomor_kamar' => strtoupper(trim($validated['nomor_kamar'])),
            'kapasitas' => $validated['kapasitas'],
            'status_kondisi' => $validated['status_kondisi'] ?? 'Baik',
            'catatan_fasilitas' => $validated['catatan_fasilitas'] ?? null,
        ]);

        return response()->json(['message' => 'Data kamar berhasil diperbarui', 'data' => $kamar]);
    }

    /**
     * DELETE /api/staff/asrama/kamar/{id}
     */
    public function deleteKamar($id): JsonResponse
    {
        $kamar = PendaftaranKamar::find($id);
        if (!$kamar) {
            return response()->json(['message' => 'Kamar tidak ditemukan'], 404);
        }

        if ($kamar->current_occupancy > 0) {
            return response()->json(['message' => 'Tidak bisa menghapus kamar yang masih memiliki penghuni.'], 422);
        }

        $kamar->delete();
        return response()->json(['message' => 'Kamar berhasil dihapus']);
    }

    /**
     * GET /api/staff/asrama/persetujuan
     * List persetujuan pending (pendaftaran baru atau mutasi) (US-06)
     */
    public function listPersetujuan(Request $request): JsonResponse
    {
        // Ambil yang statusnya pending
        $selections = PendaftaranRoomSelection::with(['user', 'kamar', 'requestedKamar'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['data' => $selections]);
    }

    /**
     * GET /api/staff/asrama/penghuni
     * List seluruh penghuni kamar (US-07)
     */
    public function listPenghuni(Request $request): JsonResponse
    {
        // Ambil yang statusnya approved
        $selections = PendaftaranRoomSelection::with(['user.student', 'kamar'])
            ->where('status', 'approved')
            ->orderBy('pendaftaran_kamar_id')
            ->get();

        return response()->json(['data' => $selections]);
    }

    /**
     * POST /api/staff/asrama/mutasi
     * Mutasi paksa siswa ke kamar lain oleh admin (US-07)
     */
    public function mutasiSiswa(Request $request): JsonResponse
    {
        $request->validate([
            'selection_id' => 'required|exists:pendaftaran_room_selections,id',
            'target_kamar_id' => 'required|exists:pendaftaran_kamar,id',
            'notes' => 'nullable|string'
        ]);

        return DB::transaction(function () use ($request) {
            $selection = PendaftaranRoomSelection::find($request->input('selection_id'));
            $targetKamar = PendaftaranKamar::find($request->input('target_kamar_id'));

            if ($targetKamar->current_occupancy >= $targetKamar->kapasitas) {
                return response()->json(['message' => 'Kamar tujuan sudah penuh.'], 422);
            }

            if ($selection->pendaftaran_kamar_id === $targetKamar->id) {
                return response()->json(['message' => 'Siswa sudah berada di kamar tersebut.'], 422);
            }

            // Kurangi kamar lama
            PendaftaranKamar::where('id', $selection->pendaftaran_kamar_id)->decrement('current_occupancy');

            // Set ke kamar baru
            $selection->pendaftaran_kamar_id = $targetKamar->id;
            $selection->requested_kamar_id = null; // Reset if they had a pending request
            $selection->status = 'approved';
            $selection->notes = $request->input('notes') ?: 'Mutasi diproses oleh Staff Asrama.';
            $selection->save();

            // Tambah kamar baru
            $targetKamar->increment('current_occupancy');

            return response()->json([
                'message' => 'Mutasi berhasil.',
                'data' => $selection->load(['kamar', 'user.student']),
            ]);
        });
    }
}
