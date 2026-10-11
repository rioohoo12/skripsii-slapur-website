<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$u1 = User::updateOrCreate(
    ['email' => 'asramaputra@gmail.com'],
    [
        'name' => 'Staff Asrama Putra',
        'role' => 'staff_asrama',
        'jenis_kelamin' => 'laki-laki',
        'password' => Hash::make('Putra123')
    ]
);

$u2 = User::updateOrCreate(
    ['email' => 'asramaputri@gmail.com'],
    [
        'name' => 'Staff Asrama Putri',
        'role' => 'staff_asrama',
        'jenis_kelamin' => 'perempuan',
        'password' => Hash::make('Putri123')
    ]
);

$u3 = User::updateOrCreate(
    ['email' => 'siswa@gmail.com'],
    [
        'name' => 'Rio Siswa Pendaftar',
        'role' => 'siswa',
        'jenis_kelamin' => 'laki-laki',
        'password' => Hash::make('password123')
    ]
);

$rio = User::find(48);
$rio->password = Hash::make('password123');
$rio->save();
echo "RIO USER: email={$rio->email}, name={$rio->name}, password=password123\n";


$controller = app(\App\Http\Controllers\Api\PendaftaranController::class);
$reqStatus = \Illuminate\Http\Request::create('/api/pendaftaran/status', 'GET');
$reqStatus->setUserResolver(fn() => $rio);
$resStatus = $controller->status($reqStatus);
echo "STATUS (200 expected): " . $resStatus->getStatusCode() . "\n";

$reqDocs = \Illuminate\Http\Request::create('/api/pendaftaran/dokumen', 'GET');
$reqDocs->setUserResolver(fn() => $rio);
$resDocs = $controller->dokumen($reqDocs);
$reqTagihan = \Illuminate\Http\Request::create('/api/siswa/tagihan', 'GET');
$reqTagihan->setUserResolver(fn() => $rio);
$resTagihan = $controller->getSiswaTagihan($reqTagihan);
echo "\n--- SISWA TAGIHAN RESPONSE ---\n";
echo "STATUS CODE: " . $resTagihan->getStatusCode() . "\n";
print_r($resTagihan->getData());


use App\Models\Student;
use App\Models\Tagihan;
use App\Models\Pembayaran;
use App\Models\PendaftaranPayment;
use Carbon\Carbon;

foreach (Student::all() as $st) {
    // 1. Tagihan Pendaftaran
    $tagihanDaftar = Tagihan::firstOrCreate(
        ['siswa_id' => $st->id, 'jenis' => 'pendaftaran'],
        [
            'nominal' => 5000000,
            'jatuh_tempo' => Carbon::now()->addDays(14)->toDateString(),
            'status' => $st->has_paid_registration ? 'lunas' : 'belum_bayar',
        ]
    );

    // Sync pembayaran pendaftaran
    $pPayment = PendaftaranPayment::where('user_id', $st->user_id)->first();
    $pStatus = $pPayment ? ($pPayment->status === 'terverifikasi' ? 'terverifikasi' : 'menunggu') : ($st->has_paid_registration ? 'terverifikasi' : null);
    
    if ($pStatus) {
        Pembayaran::updateOrCreate(
            ['tagihan_id' => $tagihanDaftar->id],
            [
                'nominal_bayar' => 3000000,
                'metode' => 'TRANSFER_BANK',
                'bukti_transfer_path' => $pPayment?->bukti_path ?? 'bukti_pendaftaran/default_bukti.png',
                'status' => $pStatus,
                'diverifikasi_oleh' => 46,
                'paid_at' => $pStatus === 'terverifikasi' ? Carbon::now() : null,
            ]
        );
        $tagihanDaftar->update(['status' => $pStatus === 'terverifikasi' ? 'lunas' : 'menunggu']);
    }

    // 2. Tagihan SPP
    $tagihanSpp = Tagihan::firstOrCreate(
        ['siswa_id' => $st->id, 'jenis' => 'SPP'],
        [
            'nominal' => 1500000,
            'jatuh_tempo' => Carbon::now()->endOfMonth()->toDateString(),
            'status' => 'belum_bayar',
        ]
    );

    // 3. Tagihan Asrama
    $tagihanAsrama = Tagihan::firstOrCreate(
        ['siswa_id' => $st->id, 'jenis' => 'asrama'],
        [
            'nominal' => 2000000,
            'jatuh_tempo' => Carbon::now()->addDays(30)->toDateString(),
            'status' => 'belum_bayar',
        ]
    );
}

echo "SYNC COMPLETED!\n";
echo "Total Tagihan: " . Tagihan::count() . "\n";
echo "Total Pembayaran: " . Pembayaran::count() . "\n";
