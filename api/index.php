<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

try {

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

    // DIAGNOSTIC: boot kernel, report provider state, then stop.
    $kernel = $app->make(Kernel::class);
    $request = Request::create('/login', 'GET');
    $app->instance('request', $request);
    $kernel->bootstrap();

    header('Content-Type: text/plain');
    $loaded = $app->getLoadedProviders();
    echo "PHP: ".PHP_VERSION."\n";
    echo "ViewServiceProvider loaded? ".(isset($loaded['Illuminate\View\ViewServiceProvider']) ? "YES" : "NO")."\n";
    echo "app.providers count: ".count(config('app.providers') ?? [])."\n";
    echo "DefaultProviders has View? ".(in_array('Illuminate\View\ViewServiceProvider', (new Illuminate\Support\DefaultProviders())->toArray()) ? "YES" : "NO")."\n";
    echo "packages.php exists? ".(is_file(__DIR__.'/../bootstrap/cache/packages.php') ? "YES" : "NO")."\n";
    echo "try view: ";
    try { echo get_class($app->make('view'))."\n"; } catch (\Throwable $e) { echo "ERR ".$e->getMessage()."\n"; }
    exit;

} catch (\Throwable $e) {
    http_response_code(500);
    header('Content-Type: text/plain');
    echo 'EXCEPTION: '.get_class($e)."\n";
    echo 'MESSAGE: '.$e->getMessage()."\n";
    exit(1);
}
