<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\Applicant;
use App\Models\Fee;
use App\Services\InvoiceService;

class InvoiceController extends Controller
{
    protected $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    public function checkStatus($nomorPendaftaran)
    {
        $applicant = Applicant::where('nomor_pendaftaran', $nomorPendaftaran)->first();
        
        if (!$applicant) {
            return response()->json(['message' => 'Pendaftar tidak ditemukan'], 404);
        }

        $invoice = Invoice::where('applicant_id', $applicant->id)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$invoice) {
            // Belum ada tagihan, buat baru
            $fee = Fee::firstOrCreate(
                ['name' => 'Biaya Pendaftaran'],
                ['amount' => 3000000]
            );
            $invoice = $this->invoiceService->createInvoice($applicant, $fee);
        }

        if ($invoice->status === 'expired' || $invoice->status === 'unpaid') {
            $token = $this->invoiceService->getPaymentLink($invoice);
            return response()->json([
                'status' => $invoice->status,
                'payment_url' => $token ? "https://app.sandbox.midtrans.com/snap/v3/redirection/{$token}" : null,
                'message' => 'Silakan lakukan pembayaran'
            ]);
        }

        return response()->json([
            'status' => $invoice->status,
            'message' => 'Pembayaran Anda sudah ' . ($invoice->status === 'paid' ? 'lunas' : 'diproses')
        ]);
    }
}
