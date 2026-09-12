<?php

return [
    /*
    | Total tagihan pendaftaran (Rp). Siswa bayar 60% untuk verifikasi.
    */
    'total_tagihan' => (float) env('PENDAFTARAN_TOTAL_TAGIHAN', 5_000_000),

    'rekening_verifikasi' => env('PENDAFTARAN_REKENING', 'Bank XXX - 1234567890 a.n. SLAPUR'),
];
