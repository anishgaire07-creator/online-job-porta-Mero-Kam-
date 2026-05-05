<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

use MeroKam\Core\Auth;
use MeroKam\Core\Response;
use MeroKam\Models\ApplicationModel;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::json(['ok' => false, 'error' => 'Method not allowed'], 405);
}

$user = Auth::requireRole('employer');
$raw = json_decode(file_get_contents('php://input'), true) ?? [];
$id = (int) ($raw['application_id'] ?? 0);
$status = $raw['status'] ?? '';
if ($id < 1 || !in_array($status, ['pending', 'shortlisted', 'rejected', 'hired'], true)) {
    Response::json(['ok' => false, 'error' => 'Invalid input'], 422);
}

$ok = ApplicationModel::updateStatus($id, $user['id'], $status);
Response::json(['ok' => $ok]);
