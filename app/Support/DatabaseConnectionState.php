<?php

namespace App\Support;

use Illuminate\Database\QueryException;
use PDOException;
use Throwable;

class DatabaseConnectionState
{
    public static function isUnavailable(Throwable $exception): bool
    {
        if (! $exception instanceof QueryException && ! $exception instanceof PDOException) {
            return false;
        }

        $message = strtolower($exception->getMessage());

        return str_contains($message, 'sqlstate[hy000] [2002]')
            || str_contains($message, 'connection refused')
            || str_contains($message, 'actively refused')
            || str_contains($message, 'no connection could be made')
            || str_contains($message, "can't connect to mysql server")
            || str_contains($message, 'server has gone away')
            || str_contains($message, 'lost connection')
            || str_contains($message, 'connection timed out')
            || str_contains($message, 'php_network_getaddresses');
    }

    public static function loginHelpMessage(): string
    {
        return 'Database MySQL belum aktif atau sedang bermasalah. Jalankan MySQL di XAMPP lalu coba login lagi.';
    }
}
