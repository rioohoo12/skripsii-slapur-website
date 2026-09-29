<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Applicant;
use App\Models\Fee;
use App\Models\Payment;
use Midtrans\Config;
use Midtrans\Snap;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class InvoiceService
{
    public function __construct()
    {
        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false);
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    /**
     * Create an invoice for an applicant
     */
    public function createInvoice(Applicant $applicant, Fee $fee): Invoice
    {
        return Invoice::create([
            'applicant_id' => $applicant->id,
            'fee_id' => $fee->id,
            'total_amount' => $fee->amount,
            'status' => 'unpaid'
        ]);
    }

    /**
     * Generate Midtrans payment link/token
     */
    public function getPaymentLink(Invoice $invoice)
    {
        $applicant = collect([
            'first_name' => $invoice->applicant->nama_lengkap ?? 'Calon Siswa',
            'email' => $invoice->applicant->email ?? 'noreply@slapur.com',
            'phone' => $invoice->applicant->no_hp ?? '0800000000'
        ]);

        $orderId = 'INV-' . $invoice->id . '-' . time();

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $invoice->total_amount,
            ],
            'customer_details' => $applicant->toArray(),
            'item_details' => [
                [
                    'id' => $invoice->fee_id,
                    'price' => $invoice->total_amount,
                    'quantity' => 1,
                    'name' => 'Pembayaran: ' . ($invoice->fee->name ?? 'Biaya'),
                ]
            ],
        ];

        try {
            $snapToken = Snap::getSnapToken($params);
            
            // Create payment record
            Payment::create([
                'invoice_id' => $invoice->id,
                'amount' => $invoice->total_amount,
                'payment_status' => 'menunggu',
                'payment_type' => 'midtrans',
                'midtrans_transaction_id' => null, // will be updated by webhook
                'order_id' => $orderId
            ]);

            return $snapToken;
        } catch (\Exception $e) {
            Log::error('Midtrans Snap Error: ' . $e->getMessage());
            return null;
        }
    }
}
