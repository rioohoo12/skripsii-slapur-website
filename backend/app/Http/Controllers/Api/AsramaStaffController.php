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
     * Tentukan asrama yang dikelola: 'putra' (kamar A-C) atau 'putri' (kamar D-F).
     * Staff asrama selalu dikunci ke asramanya sendiri; admin boleh memilih via ?asrama_type=.
     */
    private function getAsramaType(Request $request): string
    {
        $user = $request->user();
        if ($user && $user->role === 'staff_asrama') {
            return (str_contains(strtolower($user->email), 'putri') || $user->jenis_kelamin === 'perempuan')
                ? 'putri'
                : 'putra';
        }
        return strtolower((string) $request->input('asrama_type')) === 'putri' ? 'putri' : 'putra';
    }

    private function prefixesFor(string $asramaType): array
    {
        return $asramaType === 'putri' ? ['D', 'E', 'F'] : ['A', 'B', 'C'];
    }

    private function kamarBelongsTo(string $nomorKamar, string $asramaType): bool
    {
        return in_array(strtoupper(substr(trim($nomorKamar), 0, 1)), $this->prefixesFor($asramaType), true);
    }

    /**
     * GET /api/staff/asrama/dashboard
     * Mengambil statistik untuk dashboard Asrama (Putra vs Putri)
     */
    public function dashboard(Request $request): JsonResponse
    {
        $asramaType = $this->getAsramaType($request);
        $prefixes = $asramaType === 'putri' ? ['D', 'E', 'F'] : ['A', 'B', 'C'];

        $kamar = PendaftaranKamar::where(function($q) use ($prefixes) {
            foreach ($prefixes as $p) {
                $q->orWhere('nomor_kamar', 'LIKE', $p . '%');
            }
        })->get();

        $totalKamar = $kamar->count();
        $totalPenghuni = $kamar->sum('current_occupancy');
        
        $kamarKosong = $kamar->filter(function ($k) {
            return $k->current_occupancy < $k->kapasitas;
        })->count();

        $kamarIds = $kamar->pluck('id')->toArray();

        $permintaanPindah = PendaftaranRoomSelection::whereIn('pendaftaran_kamar_id', $kamarIds)
            ->whereNotNull('requested_kamar_id')
            ->where('status', 'pending')
            ->count();
        
        $pendaftarBaru = PendaftaranRoomSelection::whereIn('pendaftaran_kamar_id', $kamarIds)
            ->whereNull('requested_kamar_id')
            ->where('status', 'pending')
            ->count();

        if ($asramaType === 'putri') {
            $kapasitasGedung = [
                [
                    'gedung' => 'Gedung D (Krisan)',
                    'occupancy' => $this->calculateOccupancyPercentage($kamar, 'D'),
                ],
                [
                    'gedung' => 'Gedung E (Melati)',
                    'occupancy' => $this->calculateOccupancyPercentage($kamar, 'E'),
                ],
                [
                    'gedung' => 'Gedung F (Mawar)',
                    'occupancy' => $this->calculateOccupancyPercentage($kamar, 'F'),
                ],
            ];
        } else {
            $kapasitasGedung = [
                [
                    'gedung' => 'Gedung A (Anex)',
                    'occupancy' => $this->calculateOccupancyPercentage($kamar, 'A'),
                ],
                [
                    'gedung' => 'Gedung B (Cendrawasih)',
                    'occupancy' => $this->calculateOccupancyPercentage($kamar, 'B'),
                ],
                [
                    'gedung' => 'Gedung C (Hawk)',
                    'occupancy' => $this->calculateOccupancyPercentage($kamar, 'C'),
                ],
            ];
        }

        return response()->json([
            'asrama_type' => $asramaType,
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
     * List Master Kamar (Putra / Putri)
     */
    public function listKamar(Request $request): JsonResponse
    {
        $asramaType = $this->getAsramaType($request);
        $prefixes = $asramaType === 'putri' ? ['D', 'E', 'F'] : ['A', 'B', 'C'];

        $kamar = PendaftaranKamar::where(function($q) use ($prefixes) {
            foreach ($prefixes as $p) {
                $q->orWhere('nomor_kamar', 'LIKE', $p . '%');
            }
        })->orderBy('nomor_kamar')->get();

        return response()->json([
            'data' => $kamar,
            'asrama_type' => $asramaType,
        ]);
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

        $asramaType = $this->getAsramaType($request);
        if (!$this->kamarBelongsTo($validated['nomor_kamar'], $asramaType)) {
            $label = $asramaType === 'putri' ? 'Asrama Putri (awalan D, E, F)' : 'Asrama Putra (awalan A, B, C)';
            return response()->json(['message' => "Nomor kamar harus untuk {$label}."], 422);
        }

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

        $asramaType = $this->getAsramaType($request);
        if (!$this->kamarBelongsTo($kamar->nomor_kamar, $asramaType) || !$this->kamarBelongsTo($validated['nomor_kamar'], $asramaType)) {
            return response()->json(['message' => 'Kamar ini bukan bagian dari asrama yang Anda kelola.'], 403);
        }

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
    public function deleteKamar(Request $request, $id): JsonResponse
    {
        $kamar = PendaftaranKamar::find($id);
        if (!$kamar) {
            return response()->json(['message' => 'Kamar tidak ditemukan'], 404);
        }

        if (!$this->kamarBelongsTo($kamar->nomor_kamar, $this->getAsramaType($request))) {
            return response()->json(['message' => 'Kamar ini bukan bagian dari asrama yang Anda kelola.'], 403);
        }

        if ($kamar->current_occupancy > 0) {
            return response()->json(['message' => 'Tidak bisa menghapus kamar yang masih memiliki penghuni.'], 422);
        }

        $kamar->delete();
        return response()->json(['message' => 'Kamar berhasil dihapus']);
    }

    /**
     * GET /api/staff/asrama/persetujuan
     * List persetujuan pending (pendaftaran baru atau mutasi) (Putra / Putri)
     */
    public function listPersetujuan(Request $request): JsonResponse
    {
        $asramaType = $this->getAsramaType($request);
        $prefixes = $asramaType === 'putri' ? ['D', 'E', 'F'] : ['A', 'B', 'C'];

        $selections = PendaftaranRoomSelection::with(['user', 'kamar', 'requestedKamar'])
            ->where('status', 'pending')
            ->whereHas('kamar', function($q) use ($prefixes) {
                $q->where(function($sub) use ($prefixes) {
                    foreach ($prefixes as $p) {
                        $sub->orWhere('nomor_kamar', 'LIKE', $p . '%');
                    }
                });
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $selections,
            'asrama_type' => $asramaType,
        ]);
    }

    /**
     * GET /api/staff/asrama/penghuni
     * List seluruh penghuni kamar (Putra / Putri)
     */
    public function listPenghuni(Request $request): JsonResponse
    {
        $asramaType = $this->getAsramaType($request);
        $prefixes = $asramaType === 'putri' ? ['D', 'E', 'F'] : ['A', 'B', 'C'];

        $selections = PendaftaranRoomSelection::with(['user.student', 'kamar'])
            ->where('status', 'approved')
            ->whereHas('kamar', function($q) use ($prefixes) {
                $q->where(function($sub) use ($prefixes) {
                    foreach ($prefixes as $p) {
                        $sub->orWhere('nomor_kamar', 'LIKE', $p . '%');
                    }
                });
            })
            ->orderBy('pendaftaran_kamar_id')
            ->get();

        return response()->json([
            'data' => $selections,
            'asrama_type' => $asramaType,
        ]);
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

            $asramaType = $this->getAsramaType($request);
            $currentNomor = optional(PendaftaranKamar::find($selection->pendaftaran_kamar_id))->nomor_kamar ?? '';
            if (!$this->kamarBelongsTo($targetKamar->nomor_kamar, $asramaType)
                || ($currentNomor !== '' && !$this->kamarBelongsTo($currentNomor, $asramaType))) {
                return response()->json(['message' => 'Mutasi hanya bisa dilakukan antar kamar di asrama yang sama.'], 422);
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
