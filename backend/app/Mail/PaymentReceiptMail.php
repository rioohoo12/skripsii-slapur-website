<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Payment;
use App\Models\Invoice;

class PaymentReceiptMail extends Mailable
{
    use Queueable, SerializesModels;

    public $payment;
    public $invoice;

    public function __construct(Payment $payment, Invoice $invoice)
    {
        $this->payment = $payment;
        $this->invoice = $invoice;
    }

    public function build()
    {
        return $this->subject('Bukti Pembayaran Pendaftaran - SLAPUR')
                    ->html('
            <h2>Terima Kasih, Pembayaran Berhasil!</h2>
            <p>Halo ' . e($this->invoice->applicant->nama_lengkap) . ',</p>
            <p>Pembayaran Anda untuk <strong>' . e($this->invoice->fee->name ?? 'Pendaftaran') . '</strong> telah kami terima.</p>
            <ul>
                <li><strong>Nomor Pendaftaran:</strong> ' . e($this->invoice->applicant->nomor_pendaftaran) . '</li>
                <li><strong>Total Bayar:</strong> Rp ' . number_format($this->payment->amount, 0, ',', '.') . '</li>
                <li><strong>Waktu Pembayaran:</strong> ' . $this->payment->updated_at->format('d M Y H:i:s') . '</li>
            </ul>
            <p>Anda sekarang resmi menjadi calon siswa terverifikasi. Kami akan menghubungi Anda untuk instruksi lebih lanjut.</p>
        ');
    }
}
