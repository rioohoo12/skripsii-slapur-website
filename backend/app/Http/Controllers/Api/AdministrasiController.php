<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Student;
use App\Models\DokumenSiswa;
use App\Models\Pembayaran;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdministrasiController extends Controller
{
    /**
     * POST /api/administrasi/login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Email atau password salah.'], 401);
        }

        if (!in_array($user->role, ['staff', 'admin', 'super_admin'])) {
            return response()->json(['message' => 'Akses ditolak. Hanya untuk staff administrasi.'], 403);
        }

        $token = $user->createToken('administrasi_token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'token' => $token,
            'user' => $user,
        ]);
    }

    /**
     * GET /api/administrasi/dashboard
     */
    public function dashboard(Request $request)
    {
        $waitingDocsCount = DokumenSiswa::where('status', 'menunggu')->count();
        $waitingPaymentsCount = Pembayaran::where('status', 'menunggu')->count();
        
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        // Total revenue this month
        $monthlyRevenue = Pembayaran::where('status', 'terverifikasi')
            ->whereMonth('paid_at', $currentMonth)
            ->whereYear('paid_at', $currentYear)
            ->join('tagihans', 'pembayarans.tagihan_id', '=', 'tagihans.id')
            ->sum('tagihans.nominal');

        // New applicants from chatbot (simulated by checking if they don't have password or marked by chatbot source)
        // Here we just count recent students as proxy if source column isn't there
        $recentApplicants = Student::whereMonth('created_at', $currentMonth)->count();

        // Oldest pending actions
        $oldestDocs = DokumenSiswa::with(['student', 'jenisDokumen'])
            ->where('status', 'menunggu')
            ->orderBy('created_at', 'asc')
            ->take(5)
            ->get();
            
        $oldestPayments = Pembayaran::with(['tagihan.student'])
            ->where('status', 'menunggu')
            ->orderBy('created_at', 'asc')
            ->take(5)
            ->get();

        // Data for chart
        $revenuePerMonth = [];
        $paymentStatusChart = [
            'terverifikasi' => Pembayaran::where('status', 'terverifikasi')->count(),
            'menunggu' => Pembayaran::where('status', 'menunggu')->count(),
            'ditolak' => Pembayaran::where('status', 'ditolak')->count(),
        ];

        return response()->json([
            'stats' => [
                'waiting_docs' => $waitingDocsCount,
                'waiting_payments' => $waitingPaymentsCount,
                'monthly_revenue' => $monthlyRevenue,
                'recent_applicants' => $recentApplicants,
            ],
            'action_needed' => [
                'docs' => $oldestDocs,
                'payments' => $oldestPayments,
            ],
            'charts' => [
                'payment_status' => $paymentStatusChart,
                'revenue_per_month' => $revenuePerMonth,
            ]
        ]);
    }

    // -- SISWA --
    public function getSiswa(Request $request)
    {
        $query = Student::query();

        if ($request->search) {
            $query->where('full_name', 'like', '%' . $request->search . '%')
                  ->orWhere('nomor_pendaftaran', 'like', '%' . $request->search . '%');
        }

        if ($request->status_pendaftaran) {
            $query->where('status_pendaftaran', $request->status_pendaftaran);
        }

        if ($request->jenjang) {
            $query->where('kelas_yang_didaftar', $request->jenjang);
        }

        $sortField = $request->sort_by ?? 'created_at';
        $sortOrder = $request->sort_order ?? 'desc';
        $query->orderBy($sortField, $sortOrder);

        $siswa = $query->paginate($request->per_page ?? 10);
        return response()->json($siswa);
    }

    public function getSiswaDetail($id)
    {
        $siswa = Student::with([
            'dokumenSiswa.jenisDokumen',
            'dokumenSiswa.verifikator:id,name',
            'tagihans.pembayarans',
        ])->findOrFail($id);
        
        $riwayat = \App\Models\AuditLog::where('target_type', 'Student')->where('target_id', $id)->orderBy('created_at', 'desc')->get();

        return response()->json([
            'siswa' => $siswa,
            'riwayat' => $riwayat
        ]);
    }

    public function updateSiswa(Request $request, $id)
    {
        $siswa = Student::findOrFail($id);
        $oldData = $siswa->toArray();

        // Validasi
        $validated = $request->validate([
            'full_name' => 'sometimes|required|string',
            'status_pendaftaran' => 'sometimes|required|string',
            'kelas_yang_didaftar' => 'sometimes|required|integer',
        ]);

        $siswa->update($validated);

        \App\Models\AuditLog::create([
            'user_id' => $request->user()->id,
            'aksi' => 'Update Data Siswa',
            'target_type' => 'Student',
            'target_id' => $siswa->id,
            'data_lama' => $oldData,
            'data_baru' => $siswa->toArray(),
        ]);

        return response()->json(['message' => 'Data siswa berhasil diperbarui', 'siswa' => $siswa]);
    }

    // -- DOKUMEN --
    public function getDokumen(Request $request)
    {
        $query = DokumenSiswa::with(['student', 'jenisDokumen']);

        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->jenis_dokumen_id) {
            $query->where('jenis_dokumen_id', $request->jenis_dokumen_id);
        }

        // Oldest first for queuing
        $query->orderBy('created_at', 'asc');

        $paginated = $query->paginate($request->per_page ?? 10);
        $paginated->getCollection()->transform(function ($d) {
            $path = $d->file_path ? ltrim($d->file_path, '/') : null;
            $d->file_url = $path ? url('storage/' . $path) : null;
            $d->file_relative_url = $path ? '/storage/' . $path : null;
            return $d;
        });

        return response()->json($paginated);
    }

    public function getDokumenDetail($id)
    {
        $dokumen = DokumenSiswa::with(['student', 'jenisDokumen', 'verifikator:id,name'])->findOrFail($id);
        
        $response = $dokumen->toArray();
        $path = $dokumen->file_path ? ltrim($dokumen->file_path, '/') : null;
        
        $exists = $path && \Illuminate\Support\Facades\Storage::disk('public')->exists($path);
        
        // Auto-heal if file is not directly found at path
        if (!$exists && $path && $dokumen->student) {
            $dir = dirname($path);
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($dir)) {
                $files = \Illuminate\Support\Facades\Storage::disk('public')->files($dir);
                if (!empty($files)) {
                    $path = $files[0];
                    $exists = true;
                    $dokumen->file_path = $path;
                    $dokumen->save();
                    $response['file_path'] = $path;
                }
            }
        }
        
        $fileSize = null;
        $mimeType = null;
        if ($exists) {
            try {
                $fileSize = \Illuminate\Support\Facades\Storage::disk('public')->size($path);
                $mimeType = \Illuminate\Support\Facades\Storage::disk('public')->mimeType($path);
            } catch (\Exception $e) {}
        }
        
        $response['file_url'] = $path ? url('storage/' . $path) : null;
        $response['file_relative_url'] = $path ? '/storage/' . $path : null;
        $response['file_name'] = $path ? basename($path) : null;
        $response['file_size'] = $fileSize;
        $response['file_size_formatted'] = $fileSize ? ($fileSize > 1048576 ? round($fileSize / 1048576, 2) . ' MB' : round($fileSize / 1024, 1) . ' KB') : null;
        $response['file_mime'] = $mimeType;
        $response['file_exists'] = $exists;
        
        // Other documents of this same student for quick switcher
        $response['other_documents'] = DokumenSiswa::where('siswa_id', $dokumen->siswa_id)
            ->with('jenisDokumen')
            ->orderBy('jenis_dokumen_id', 'asc')
            ->get()
            ->map(function ($other) use ($dokumen) {
                $oPath = $other->file_path ? ltrim($other->file_path, '/') : null;
                return [
                    'id' => $other->id,
                    'jenis_nama' => $other->jenisDokumen?->nama ?? 'Dokumen',
                    'status' => $other->status,
                    'file_name' => $oPath ? basename($oPath) : null,
                    'is_current' => $other->id === $dokumen->id,
                ];
            });

        return response()->json($response);
    }

    public function verifikasiDokumen(Request $request, $id)
    {
        $dokumen = DokumenSiswa::findOrFail($id);
        $oldData = $dokumen->toArray();

        $validated = $request->validate([
            'status' => 'required|in:disetujui,ditolak',
            'catatan' => 'required_if:status,ditolak|nullable|string',
        ]);

        $dokumen->update([
            'status' => $validated['status'],
            'catatan' => $validated['catatan'] ?? null,
            'diverifikasi_oleh' => $request->user()->id,
            'diverifikasi_at' => Carbon::now(),
        ]);

        \App\Models\AuditLog::create([
            'user_id' => $request->user()->id,
            'aksi' => "Verifikasi Dokumen: {$validated['status']}",
            'target_type' => 'DokumenSiswa',
            'target_id' => $dokumen->id,
            'data_lama' => $oldData,
            'data_baru' => $dokumen->toArray(),
        ]);

        // Email logic can be dispatched here
        // e.g. Mail::to($dokumen->student->email)->queue(new DokumenVerifikasiMail($dokumen));

        return response()->json(['message' => 'Dokumen berhasil diverifikasi', 'dokumen' => $dokumen]);
    }

    // -- PEMBAYARAN --
    public function getPembayaran(Request $request)
    {
        $query = Pembayaran::with(['tagihan.student', 'verifikator:id,name']);

        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->metode) {
            $query->where('metode', $request->metode);
        }
        if ($request->search) {
            $query->whereHas('tagihan.student', function($q) use ($request) {
                $q->where('full_name', 'like', '%' . $request->search . '%')
                  ->orWhere('nomor_pendaftaran', 'like', '%' . $request->search . '%');
            })->orWhere('midtrans_order_id', 'like', '%' . $request->search . '%');
        }

        $query->orderBy('created_at', 'desc');

        $paginated = $query->paginate($request->per_page ?? 10);
        $paginated->getCollection()->transform(function ($item) {
            $path = $item->bukti_transfer_path ? ltrim($item->bukti_transfer_path, '/') : null;
            $item->bukti_transfer_url = $path ? url('storage/' . $path) : null;
            $item->bukti_transfer_relative_url = $path ? '/storage/' . $path : null;
            $item->nominal_display = (float) ($item->nominal_bayar ?: ($item->tagihan?->nominal ?? 0));
            return $item;
        });

        return response()->json($paginated);
    }

    public function getPembayaranDetail($id)
    {
        $pembayaran = Pembayaran::with(['tagihan.student', 'verifikator:id,name'])->findOrFail($id);
        $res = $pembayaran->toArray();

        $path = $pembayaran->bukti_transfer_path ? ltrim($pembayaran->bukti_transfer_path, '/') : null;
        $exists = $path && \Illuminate\Support\Facades\Storage::disk('public')->exists($path);

        $res['bukti_transfer_url'] = $path ? url('storage/' . $path) : null;
        $res['bukti_transfer_relative_url'] = $path ? '/storage/' . $path : null;
        $res['file_exists'] = $exists;
        $res['file_name'] = $path ? basename($path) : null;
        $res['nominal_display'] = (float) ($pembayaran->nominal_bayar ?: ($pembayaran->tagihan?->nominal ?? 0));

        // Other payments of this student
        if ($pembayaran->tagihan && $pembayaran->tagihan->siswa_id) {
            $res['other_payments'] = Pembayaran::whereHas('tagihan', function($q) use ($pembayaran) {
                $q->where('siswa_id', $pembayaran->tagihan->siswa_id);
            })->with('tagihan')->orderBy('id', 'asc')->get()->map(function($other) use ($pembayaran) {
                return [
                    'id' => $other->id,
                    'tagihan_jenis' => $other->tagihan?->jenis ?? 'Tagihan',
                    'nominal' => (float) ($other->nominal_bayar ?: ($other->tagihan?->nominal ?? 0)),
                    'status' => $other->status,
                    'is_current' => $other->id === $pembayaran->id,
                ];
            });
        } else {
            $res['other_payments'] = [];
        }

        return response()->json($res);
    }

    public function verifikasiPembayaran(Request $request, $id)
    {
        $pembayaran = Pembayaran::with('tagihan.student')->findOrFail($id);
        $oldData = $pembayaran->toArray();

        $validated = $request->validate([
            'status' => 'required|in:terverifikasi,ditolak',
            'catatan' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $pembayaran->update([
                'status' => $validated['status'],
                'catatan' => $validated['catatan'] ?? null,
                'diverifikasi_oleh' => $request->user()->id,
                'paid_at' => $validated['status'] === 'terverifikasi' ? Carbon::now() : null,
            ]);

            $tagihan = $pembayaran->tagihan;
            if ($tagihan) {
                if ($validated['status'] === 'terverifikasi') {
                    $tagihan->update(['status' => 'lunas']);

                    if ($tagihan->jenis === 'pendaftaran') {
                        $tagihan->student->update([
                            'has_paid_registration' => 1,
                            'status_pendaftaran' => 'kamar',
                        ]);
                        \App\Models\PendaftaranPayment::where('user_id', $tagihan->student->user_id)->update([
                            'status' => 'terverifikasi',
                            'verified_at' => Carbon::now(),
                        ]);
                    }
                } else if ($validated['status'] === 'ditolak') {
                    $tagihan->update(['status' => 'ditolak']);
                    if ($tagihan->jenis === 'pendaftaran') {
                        \App\Models\PendaftaranPayment::where('user_id', $tagihan->student->user_id)->update([
                            'status' => 'ditolak',
                        ]);
                    }
                }
            }

            \App\Models\AuditLog::create([
                'user_id' => $request->user()->id,
                'aksi' => "Verifikasi Pembayaran: {$validated['status']}",
                'target_type' => 'Pembayaran',
                'target_id' => $pembayaran->id,
                'data_lama' => $oldData,
                'data_baru' => $pembayaran->toArray(),
            ]);

            DB::commit();

            return response()->json(['message' => 'Status pembayaran berhasil diperbarui', 'pembayaran' => $pembayaran]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()], 500);
        }
    }

    // -- TAGIHAN --
    public function getTagihan(Request $request)
    {
        $query = \App\Models\Tagihan::with('student');

        if ($request->status) {
            $query->where('status', $request->status);
        }
        
        $query->orderBy('created_at', 'desc');

        return response()->json($query->paginate($request->per_page ?? 10));
    }

    public function buatTagihan(Request $request)
    {
        $validated = $request->validate([
            'siswa_id' => 'required|exists:students,id',
            'jenis' => 'required|string',
            'nominal' => 'required|numeric',
            'jatuh_tempo' => 'required|date',
        ]);

        $tagihan = \App\Models\Tagihan::create(array_merge($validated, ['status' => 'belum_bayar']));

        return response()->json(['message' => 'Tagihan berhasil dibuat', 'tagihan' => $tagihan]);
    }

    // -- LAPORAN --
    public function laporan($type, Request $request)
    {
        if ($type === 'pembayaran') {
            $query = Pembayaran::with(['tagihan.student'])->where('status', 'terverifikasi');
            
            if ($request->start_date && $request->end_date) {
                $query->whereBetween('paid_at', [$request->start_date, $request->end_date]);
            }
            
            $data = $query->get();
            $total = $query->join('tagihans', 'pembayarans.tagihan_id', '=', 'tagihans.id')->sum('tagihans.nominal');

            return response()->json([
                'data' => $data,
                'summary' => ['total_pemasukan' => $total]
            ]);
        }

        if ($type === 'dokumen') {
            // Siswa yang dokumennya < 4
            $siswa = Student::withCount('dokumenSiswa')
                ->having('dokumen_siswa_count', '<', 4)
                ->get();
            return response()->json(['data' => $siswa]);
        }

        return response()->json(['message' => 'Tipe laporan tidak valid'], 400);
    }
}
