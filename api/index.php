<?php

if ((string) getenv('VERCEL') === '1') {
    $storagePath = '/tmp/laravel-storage';
    $compiledViewPath = $storagePath.'/framework/views';

    foreach ([
        $compiledViewPath,
        $storagePath.'/framework/cache/data',
        $storagePath.'/framework/sessions',
        $storagePath.'/logs',
    ] as $path) {
        if (! is_dir($path) && ! @mkdir($path, 0777, true) && ! is_dir($path)) {
            throw new RuntimeException("Unable to create Laravel runtime directory: {$path}");
        }
    }

    foreach ([
        'LARAVEL_STORAGE_PATH' => $storagePath,
        'VIEW_COMPILED_PATH' => $compiledViewPath,
    ] as $key => $value) {
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
        putenv("{$key}={$value}");
    }

    $_SERVER['SCRIPT_NAME'] = '/index.php';
    $_SERVER['PHP_SELF'] = '/index.php';
}

if ($_SERVER['REQUEST_URI'] === '/run-migrations-12345') {
    require __DIR__.'/../vendor/autoload.php';
    $app = require_once __DIR__.'/../bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    try {
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        echo "Migrations completed: " . \Illuminate\Support\Facades\Artisan::output();
    } catch (\Throwable $e) {
        echo "Migration Error: " . $e->getMessage();
    }
    exit;
}

try {
    require __DIR__.'/../public/index.php';
} catch (\Throwable $e) {
    http_response_code(500);
    echo "<h1>CRITICAL STARTUP ERROR</h1>";
    echo "<p><strong>Message:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>File:</strong> " . $e->getFile() . ":" . $e->getLine() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    echo "<hr><h3>Diagnostic Data:</h3>";
    echo "APP_KEY Starts With: " . substr(getenv('APP_KEY'), 0, 10) . "...<br>";
    echo "DB_HOST: " . getenv('DB_HOST') . "<br>";
}
