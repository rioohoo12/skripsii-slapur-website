<?php

namespace App\Services\Chatbot\Actions;

use App\Services\Chatbot\Contracts\ActionInterface;
use App\Models\ChatSession;
use App\Models\Payment;
use App\Models\Invoice;
use Illuminate\Support\Facades\Log;

class PaymentAction implements ActionInterface
{
    public function execute(ChatSession $session, array $data)
    {
        try {
            $nomorPendaftaran = $data['nomor_pendaftaran'] ?? null;
            
            if (!$nomorPendaftaran) {
                return [
                    'status' => 'error',
                    'message_key' => 'payment_missing_data',
                    'data' => []
                ];
            }

            // In a real scenario, this would query the DB for the invoice linked to the applicant
            // For now, we simulate saving the payment verification request
            
            /*
            $invoice = Invoice::where('nomor_pendaftaran', $nomorPendaftaran)->first();
            if ($invoice) {
                Payment::create([
                    'invoice_id' => $invoice->id,
                    'amount' => 250000,
                    'status' => 'pending_verification',
                    'payment_method' => $data['metode_bayar'] ?? 'Transfer',
                ]);
            }
            */

            return [
                'status' => 'success',
                'message_key' => 'payment_success',
                'data' => [
                    'invoice' => $nomorPendaftaran,
                    'status' => 'pending_verification'
                ]
            ];
        } catch (\Exception $e) {
            Log::error('PaymentAction Error: ' . $e->getMessage());
            return [
                'status' => 'error',
                'message_key' => 'payment_failed',
                'data' => []
            ];
        }
    }
}
