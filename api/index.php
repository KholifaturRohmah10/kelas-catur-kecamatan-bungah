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

require __DIR__.'/../public/index.php';
