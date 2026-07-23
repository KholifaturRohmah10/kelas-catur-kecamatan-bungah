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
