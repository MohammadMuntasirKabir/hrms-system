<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

try {

    // On Vercel the runtime filesystem is read-only (except /tmp), so point
    // Laravel's storage path at a writable location.
    if (getenv('VERCEL') !== false || isset($_SERVER['VERCEL'])) {
        $tmp = '/tmp/laravel-storage';
        if (! is_dir($tmp)) {
            mkdir($tmp, 0755, true);
            foreach (['app', 'framework', 'logs'] as $d) {
                mkdir($tmp.'/'.$d, 0755, true);
            }
            mkdir($tmp.'/framework/cache', 0755, true);
            mkdir($tmp.'/framework/sessions', 0755, true);
            mkdir($tmp.'/framework/views', 0755, true);
        }
        putenv("LARAVEL_STORAGE_PATH={$tmp}");
    }

    define('LARAVEL_START', microtime(true));

    if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
        require $maintenance;
    }

    require __DIR__.'/../vendor/autoload.php';

    $app = require_once __DIR__.'/../bootstrap/app.php';

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

    if (getenv('APP_DEBUG') !== 'false') {
        echo 'EXCEPTION: '.get_class($e)."\n";
        echo 'MESSAGE: '.$e->getMessage()."\n";
        echo 'FILE: '.$e->getFile().':'.$e->getLine()."\n";
        echo "TRACE:\n".$e->getTraceAsString()."\n";
    } else {
        echo 'Internal Server Error';
    }

    exit(1);
}
