<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $u = App\Models\User::where('email', 'hartihartono@gmail.com')->first();
    echo json_encode([
        'user_id' => $u->id,
        'profile' => App\Models\PendaftaranProfile::where('user_id', $u->id)->first(),
        'student' => Illuminate\Support\Facades\DB::table('students')->where('user_id', $u->id)->first()
    ]);
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . " " . $e->getFile() . ":" . $e->getLine();
}
