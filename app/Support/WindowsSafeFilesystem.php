<?php

namespace App\Support;

use ErrorException;
use Illuminate\Filesystem\Filesystem;

class WindowsSafeFilesystem extends Filesystem
{
    public function replace($path, $content, $mode = null): void
    {
        clearstatcache(true, $path);

        $path = realpath($path) ?: $path;

        $tempPath = tempnam(dirname($path), basename($path));

        if (! is_null($mode)) {
            @chmod($tempPath, $mode);
        } else {
            @chmod($tempPath, 0777 - umask());
        }

        file_put_contents($tempPath, $content);

        if (@rename($tempPath, $path)) {
            return;
        }

        if (DIRECTORY_SEPARATOR === '\\' && @copy($tempPath, $path)) {
            @unlink($tempPath);

            return;
        }

        $error = error_get_last();

        @unlink($tempPath);

        throw new ErrorException($error['message'] ?? "Unable to replace the file at path {$path}.");
    }
}
