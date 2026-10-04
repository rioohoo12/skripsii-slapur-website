<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $u = App\Models\User::where('email', 'sitinurbaya@gmail.com')->first();
    Laravel\Sanctum\Sanctum::actingAs($u, ['*']);
    
    $req = Illuminate\Http\Request::create('/api/guru/kelas/7', 'GET');
    $res = app()->handle($req);
    
    echo "STATUS: " . $res->getStatusCode() . "\n";
    echo "CONTENT: " . $res->getContent() . "\n";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . " " . $e->getFile() . ":" . $e->getLine();
}
