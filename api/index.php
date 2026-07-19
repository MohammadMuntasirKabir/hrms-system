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

    // TEMP DIAGNOSTIC: enable debug when secret param supplied, so Laravel
    // renders the real exception instead of the generic 500 page.
    if (($_GET['diag'] ?? null) === 'hrms_diag_2026') {
        $_ENV['APP_DEBUG'] = 'true';
        $_SERVER['APP_DEBUG'] = 'true';
        putenv('APP_DEBUG=true');
    }

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

} catch (Throwable $e) {
    http_response_code(500);
    header('Content-Type: text/plain');

    // TEMP DIAGNOSTIC: surface the real exception only when a secret query
    // param is supplied, so it is not publicly leaked.
    if (($_GET['diag'] ?? null) === 'hrms_diag_2026') {
        echo 'DIAG Exception: '.get_class($e)."\n";
        echo 'Message: '.$e->getMessage()."\n";
        echo 'File: '.$e->getFile().':'.$e->getLine()."\n";
        echo "Trace:\n".$e->getTraceAsString()."\n";
        exit(1);
    }

    // Only surface details when explicitly in debug mode AND not production.
    // Never leak stack traces (they can contain secrets / env values).
    $debug = getenv('APP_DEBUG') === 'true' && getenv('APP_ENV') !== 'production';

    if ($debug) {
        echo 'Exception: '.get_class($e)."\n";
        echo 'Message: '.$e->getMessage()."\n";
        echo 'File: '.$e->getFile().':'.$e->getLine()."\n";
    } else {
        echo 'Internal Server Error';
    }

    exit(1);
}
