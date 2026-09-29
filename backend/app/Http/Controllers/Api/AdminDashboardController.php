<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Applicant;
use App\Models\Payment;
use App\Models\Invoice;

class AdminDashboardController extends Controller
{
    public function getApplicants(Request $request)
    {
        $status = $request->query('status');
        
        $query = Applicant::query();
        if ($status) {
            $query->where('status', $status);
        }
        
        return response()->json([
            'data' => $query->orderBy('created_at', 'desc')->paginate(20)
        ]);
    }

    public function getTransactions(Request $request)
    {
        $status = $request->query('status');
        
        $query = Payment::with('invoice.applicant');
        if ($status) {
            $query->where('payment_status', $status);
        }
        
        return response()->json([
            'data' => $query->orderBy('created_at', 'desc')->paginate(20)
        ]);
    }

    public function exportPayments()
    {
        // Simple CSV export logic
        $payments = Payment::with('invoice.applicant')->get();
        $csvData = "ID,Nomor Pendaftaran,Nama Lengkap,Nominal,Status,Waktu Bayar\n";
        
        foreach ($payments as $p) {
            $applicant = $p->invoice->applicant ?? null;
            $csvData .= sprintf(
                "%s,%s,%s,%s,%s,%s\n",
                $p->id,
                $applicant->nomor_pendaftaran ?? '-',
                $applicant->nama_lengkap ?? '-',
                $p->amount,
                $p->payment_status,
                $p->verified_at ?? '-'
            );
        }
        
        return response($csvData)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="laporan_pembayaran.csv"');
    }

    public function manualMarkPayment(Request $request, $id)
    {
        $request->validate(['status' => 'required|string']);
        
        $payment = Payment::findOrFail($id);
        $payment->update(['payment_status' => $request->status]);
        
        if ($request->status === 'terverifikasi') {
            $payment->update(['verified_at' => now()]);
            $invoice = Invoice::find($payment->invoice_id);
            if ($invoice) {
                $invoice->update(['status' => 'paid']);
                if ($invoice->applicant) {
                    $invoice->applicant->update(['status' => 'verified']);
                }
            }
        }
        
        return response()->json(['message' => 'Status pembayaran berhasil diubah manual', 'data' => $payment]);
    }
}
