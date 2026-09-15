<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Payment;
use App\Models\Student;
use Midtrans\Config;
use Midtrans\Snap;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct()
    {
        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false);
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    /**
     * POST /api/v1/payments
     */
    public function createTransaction(Request $request): JsonResponse
    {
        $user = $request->user();
        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return response()->json(['message' => 'Student data not found.'], 404);
        }

        // Cek apakah ada tagihan pending atau sudah lunas
        $existingPayment = Payment::where('student_id', $student->id)
            ->whereIn('transaction_status', ['pending', 'settlement', 'capture'])
            ->first();

        if ($existingPayment && in_array($existingPayment->transaction_status, ['settlement', 'capture'])) {
            return response()->json(['message' => 'Anda sudah melakukan pembayaran.'], 422);
        }

        $orderId = $existingPayment->order_id ?? 'INV-' . date('Ymd') . '-' . strtoupper(uniqid());
        $grossAmount = 3000000; // Contoh nominal 60% pendaftaran (3.000.000)

        // Parameter Snap Midtrans
        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $grossAmount,
            ],
            'customer_details' => [
                'first_name' => $student->full_name,
                'email' => $user->email,
                'phone' => $student->no_telp_ortu,
            ],
            'item_details' => [
                [
                    'id' => 'DP-REG',
                    'price' => $grossAmount,
                    'quantity' => 1,
                    'name' => 'Pembayaran Pendaftaran (60%)',
                ]
            ],
        ];

        try {
            $snapToken = Snap::getSnapToken($params);

            // Simpan atau update record
            $payment = Payment::updateOrCreate(
                ['order_id' => $orderId],
                [
                    'student_id' => $student->id,
                    'amount' => $grossAmount,
                    'payment_type' => 'pendaftaran',
                    'payment_status' => 'menunggu',
                    'transaction_status' => 'pending',
                ]
            );

            return response()->json([
                'snap_token' => $snapToken,
                'payment' => $payment
            ]);
        } catch (\Exception $e) {
            Log::error('Midtrans Error: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal membuat transaksi pembayaran.'], 500);
        }
    }

    /**
     * POST /api/webhooks/midtrans
     */
    public function webhook(Request $request): JsonResponse
    {
        $payload = $request->all();
        
        $orderId = $payload['order_id'] ?? '';
        $statusCode = $payload['status_code'] ?? '';
        $grossAmount = $payload['gross_amount'] ?? '';
        $serverKey = env('MIDTRANS_SERVER_KEY');
        $signatureKey = $payload['signature_key'] ?? '';

        // Verifikasi Signature
        $calculatedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
        
        if ($calculatedSignature !== $signatureKey) {
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $payment = Payment::where('order_id', $orderId)->first();
        if (!$payment) {
            return response()->json(['message' => 'Payment not found'], 404);
        }

        $transactionStatus = $payload['transaction_status'] ?? '';
        $paymentType = $payload['payment_type'] ?? '';

        // Update transaction status
        $payment->transaction_status = $transactionStatus;
        $payment->payment_method = $paymentType;
        $payment->midtrans_transaction_data = $payload;

        if ($transactionStatus == 'settlement' || $transactionStatus == 'capture') {
            $payment->payment_status = 'terverifikasi';
            $payment->paid_at = now();
            $payment->verified_at = now();
            
            // Update student
            if ($payment->student) {
                $payment->student->has_paid_registration = true;
                $payment->student->save();
            }
        } elseif ($transactionStatus == 'cancel' || $transactionStatus == 'deny' || $transactionStatus == 'expire') {
            $payment->payment_status = 'batal';
        }

        $payment->save();

        return response()->json(['message' => 'Webhook received']);
    }
}
