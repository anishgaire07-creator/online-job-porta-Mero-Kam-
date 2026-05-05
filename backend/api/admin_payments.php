<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

use MeroKam\Core\Auth;
use MeroKam\Core\Response;
use MeroKam\Models\PaymentModel;

Auth::requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    Response::json(['ok' => true, 'payments' => PaymentModel::listAll(500)]);
}

if ($_SERVER['REQUEST_METHOD'] === 'PATCH') {
    $raw = json_decode(file_get_contents('php://input'), true) ?? [];
    $id = (int) ($raw['id'] ?? 0);
    $status = $raw['status'] ?? '';
    if ($id < 1 || !in_array($status, ['pending', 'completed', 'failed', 'refunded'], true)) {
        Response::json(['ok' => false, 'error' => 'Invalid input'], 422);
    }
    PaymentModel::setStatus($id, $status);
    Response::json(['ok' => true]);
}

Response::json(['ok' => false, 'error' => 'Method not allowed'], 405);
