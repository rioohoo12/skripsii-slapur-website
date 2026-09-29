<?php

namespace App\Services\Chatbot\NLU;

use App\Services\Chatbot\Contracts\ExtractorInterface;

class EntityExtractor implements ExtractorInterface
{
    public function extract(string $text, array $context = []): array
    {
        $entities = [];
        $textLower = strtolower($text);

        // 1. Email Extraction
        if (preg_match('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $text, $matches)) {
            $entities['email'] = $matches[0];
        }

        // 2. Phone Extraction (08 or +62)
        $cleanPhone = preg_replace('/[\s\-]/', '', $text);
        if (preg_match('/(\+62|62|0)8[1-9][0-9]{6,11}/', $cleanPhone, $matches)) {
            $entities['no_hp'] = $matches[0];
            // Normalization
            if (str_starts_with($entities['no_hp'], '08')) {
                $entities['no_hp'] = '+62' . substr($entities['no_hp'], 1);
            } elseif (str_starts_with($entities['no_hp'], '62')) {
                $entities['no_hp'] = '+' . $entities['no_hp'];
            }
        }

        // 3. Date Extraction (Simple regex for DD-MM-YYYY or DD/MM/YYYY)
        if (preg_match('/\b(0[1-9]|[12][0-9]|3[01])[\/\- \.](0[1-9]|1[012]|jan(?:uari)?|feb(?:ruari)?|mar(?:et)?|apr(?:il)?|mei|jun(?:i)?|jul(?:i)?|agu(?:stus)?|sep(?:tember)?|okt(?:ober)?|nov(?:ember)?|des(?:ember)?)[\/\- \.]((?:19|20)\d\d)\b/i', $text, $matches)) {
            $entities['tanggal_lahir'] = $matches[0]; // Can be normalized using Carbon later
        }

        // 4. Gender Extraction
        if (preg_match('/\b(laki-laki|laki|cowo|cowok|pria|l)\b/', $textLower)) {
            $entities['jenis_kelamin'] = 'Laki-laki';
        } elseif (preg_match('/\b(perempuan|cewe|cewek|wanita|p)\b/', $textLower)) {
            $entities['jenis_kelamin'] = 'Perempuan';
        }

        // 5. Position/State-based Extraction (Name, Address)
        if (isset($context['awaiting_slot'])) {
            $slot = $context['awaiting_slot'];
            if ($slot === 'nama_lengkap') {
                // Remove prefixes like "nama saya", "aku"
                $cleanName = preg_replace('/^(nama saya|namaku|nama|adalah|yaitu|aku|saya)\s+/i', '', $text);
                if (strlen(trim($cleanName)) > 2 && !isset($entities['email']) && !isset($entities['no_hp'])) {
                    $entities['nama_lengkap'] = ucwords(trim($cleanName));
                }
            }
            if ($slot === 'jenjang' && !isset($entities['jenjang'])) {
                // Check dictionary for jenjang
                if (preg_match('/\b(smp|mts)\b/i', $text)) $entities['jenjang'] = 'SMP';
                if (preg_match('/\b(sma|smk|ma)\b/i', $text)) $entities['jenjang'] = 'SMA';
            }
            if ($slot === 'tipe_kamar' && !isset($entities['tipe_kamar'])) {
                if (preg_match('/\b(standar|standard|biasa)\b/i', $text)) $entities['tipe_kamar'] = 'Standard';
                if (preg_match('/\b(vip|eksklusif|ac)\b/i', $text)) $entities['tipe_kamar'] = 'VIP';
            }
        }

        return $entities;
    }
}
