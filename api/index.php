<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

// TEMP DIAGNOSTIC: surface bootstrap exceptions as plain text.
try {

// On Vercel the runtime filesystem is read-only (except /tmp), so point
// Laravel's storage path at a writable location.
if (getenv('VERCEL') !== false || isset($_SERVER['VERCEL'])) {
    $tmpStorage = '/tmp/laravel-storage';
    if (! is_dir($tmpStorage)) {
        mkdir($tmpStorage, 0755, true);
        foreach (['app', 'framework', 'logs'] as $dir) {
            mkdir($tmpStorage.'/'.$dir, 0755, true);
        }
        mkdir($tmpStorage.'/framework/cache', 0755, true);
        mkdir($tmpStorage.'/framework/sessions', 0755, true);
        mkdir($tmpStorage.'/framework/views', 0755, true);
    }
    putenv("LARAVEL_STORAGE_PATH={$tmpStorage}");
}

define('LARAVEL_START', microtime(true));

if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';

// Apply the writable storage path if we set one above.
if (getenv('LARAVEL_STORAGE_PATH') !== false) {
    $app->useStoragePath(getenv('LARAVEL_STORAGE_PATH'));
}

$kernel = $app->make(Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);

} catch (\Throwable $e) {
    http_response_code(500);
    header('Content-Type: text/plain');
    echo "EXCEPTION: ".get_class($e)."\n";
    echo "MESSAGE: ".$e->getMessage()."\n";
    echo "FILE: ".$e->getFile().':'.$e->getLine()."\n";
    echo "TRACE:\n".$e->getTraceAsString()."\n";
    exit(1);
}
