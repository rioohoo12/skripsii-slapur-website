<?php

namespace App\Services\Chatbot\Dialog;

use App\Models\ChatSession;
use App\Services\Chatbot\Dialog\Flows\RegistrationFlow;
use App\Services\Chatbot\Dialog\Flows\PaymentFlow;

class DialogManager
{
    protected $flows;

    public function __construct(RegistrationFlow $registrationFlow, PaymentFlow $paymentFlow)
    {
        // Inject all available flows
        $this->flows = [
            'registration' => $registrationFlow,
            'payment' => $paymentFlow,
        ];
    }

    /**
     * Route intent and current state to the appropriate flow.
     */
    public function handle(ChatSession $session, string $intent, array $entities, string $text): array
    {
        $currentFlow = $session->current_flow;

        // 1. Context Switching (Changing Flow based on strong intent)
        if ($intent === 'mulai_daftar' && $currentFlow !== 'registration') {
            $session->current_flow = 'registration';
            $session->current_step = 'init';
            $currentFlow = 'registration';
        } elseif ($intent === 'bayar' && $currentFlow !== 'payment') {
            $session->current_flow = 'payment';
            $session->current_step = 'init';
            $currentFlow = 'payment';
        }

        // 2. Delegate to the active flow
        if (isset($this->flows[$currentFlow])) {
            return $this->flows[$currentFlow]->handle($session, $intent, $entities, $text);
        }

        // 3. Global Intents (FAQ, Minta Staf, Tidak Dikenali)
        if ($intent === 'minta_staf') {
            $session->status = 'handed_off';
            return [
                'reply' => 'Baik, saya akan menyambungkan Anda dengan admin kami. Mohon tunggu sebentar...',
                'action' => 'handoff'
            ];
        }

        if ($intent === 'tanya_info') {
            return [
                'reply' => 'Pendaftaran siswa baru SLAPUR sedang dibuka. Biaya pendaftaran Rp 250.000. Untuk mendaftar, ketik "saya mau daftar".'
            ];
        }

        return [
            'reply' => 'Maaf, saya kurang paham. Anda bisa bertanya seputar info pendaftaran, atau ketik "mulai daftar".'
        ];
    }
}
