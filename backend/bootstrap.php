<?php

declare(strict_types=1);

/**
 * Shared bootstrap for API entry points.
 */

if (ob_get_level() === 0) {
    ob_start();
}

set_exception_handler(static function (\Throwable $e): void {
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
    }
    echo json_encode([
        'ok' => false,
        'error' => 'Server error',
        'detail' => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
});

spl_autoload_register(function (string $class): void {
    $prefix = 'MeroKam\\';
    $base = __DIR__ . '/';
    if (str_starts_with($class, $prefix)) {
        $parts = explode('\\', substr($class, strlen($prefix)));
        $parts = array_map('strtolower', $parts);
        $file = $base . implode('/', $parts) . '.php';
        if (is_file($file)) {
            require $file;
        }
    }
});

use MeroKam\Core\Auth;
use MeroKam\Core\Response;

Response::cors();
Auth::startSession();
