<?php

namespace App\Services\Chatbot;

use App\Models\Applicant;
use App\Models\PendaftaranPayment;
use App\Models\User;
use Illuminate\Support\Str;

class ActionHandler
{
    /**
     * Confirm and execute actions (Pendaftaran submission or Payment invoice creation).
     *
     * @param string $action 'pendaftaran' | 'pembayaran'
     * @param array $payload
     * @param User|null $user
     * @return array
     */
    public function handleConfirm(string $action, array $payload, ?User $user = null): array
    {
        if ($action === 'pendaftaran') {
            return $this->confirmRegistration($payload);
        }

        if ($action === 'pembayaran') {
            return $this->confirmPayment($payload, $user);
        }

        return [
            'success' => false,
            'message' => 'Aksi tidak dikenal.',
        ];
    }

    /**
     * Konfirmasi Pendaftaran.
     */
    protected function confirmRegistration(array $data): array
    {
        $nomorPendaftaran = 'REG-' . date('Ymd') . '-' . strtoupper(Str::random(4));

        return [
            'success' => true,
            'action' => 'pendaftaran_confirmed',
            'nomor_pendaftaran' => $nomorPendaftaran,
            'message' => "Pendaftaran berhasil dikonfirmasi! Nomor Pendaftaran Anda: **{$nomorPendaftaran}**. Silakan lanjutkan ke tahap pembayaran.",
            'data' => array_merge($data, ['nomor_pendaftaran' => $nomorPendaftaran]),
        ];
    }

    /**
     * Konfirmasi Pembayaran (Midtrans / Invoice).
     */
    protected function confirmPayment(array $data, ?User $user = null): array
    {
        $orderId = 'INV-' . time() . '-' . rand(100, 999);
        $amount = $data['amount'] ?? 250000;

        // Simulasi/Pembuatan catatan pembayaran
        $payment = PendaftaranPayment::create([
            'user_id' => $user?->id,
            'order_id' => $orderId,
            'amount' => $amount,
            'status' => 'pending',
            'payment_type' => 'bank_transfer',
        ]);

        return [
            'success' => true,
            'action' => 'pembayaran_created',
            'order_id' => $orderId,
            'amount' => $amount,
            'payment_url' => "http://localhost:5173/siswa/pendaftaran/pembayaran?order_id={$orderId}",
            'message' => "Invoice pembayaran berhasil dibuat (ID: **{$orderId}**). Nominal: Rp " . number_format($amount, 0, ',', '.') . ". Silakan lakukan transfer ke rekening virtual account sekolah.",
        ];
    }
}
