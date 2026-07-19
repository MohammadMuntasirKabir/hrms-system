<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Throwable;

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

    // Self-healing migrations: ensure the database schema is up to date on
    // every cold start. Vercel's build step has no PHP runtime, so we run
    // migrations at request time (idempotent — a no-op when nothing is
    // pending). A lock file in /tmp prevents concurrent migrations during
    // scale-out. This keeps the Neon schema in sync after every deploy.
    if (getenv('VERCEL') !== false || isset($_SERVER['VERCEL'])) {
        $migrateLock = '/tmp/laravel-migrate.lock';
        if (! file_exists($migrateLock) && ($lock = fopen($migrateLock, 'x')) !== false) {
            fclose($lock);
            try {
                Artisan::call('migrate', ['--force' => true]);
            } catch (Throwable $e) {
                // Never block requests if migration fails; log and continue.
                error_log('Migration failed: '.$e->getMessage());
            }
            @unlink($migrateLock);
        }
    }

    $kernel = $app->make(Kernel::class);

    $response = $kernel->handle(
        $request = Request::capture()
    )->send();

    $kernel->terminate($request, $response);

} catch (Throwable $e) {
    http_response_code(500);
    header('Content-Type: text/plain');

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
