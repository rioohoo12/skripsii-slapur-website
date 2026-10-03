<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::create('/api/guru/kelas/7', 'GET');
// Mock guru user
$guru = App\Models\User::where('role', 'guru')->first();
if (!$guru) {
    echo "No guru found.\n";
} else {
    $request->setUserResolver(function() use ($guru) { return $guru; });
    $response = $kernel->handle($request);
    echo "Status: " . $response->getStatusCode() . "\n";
    echo "Content: " . $response->getContent() . "\n";
}
