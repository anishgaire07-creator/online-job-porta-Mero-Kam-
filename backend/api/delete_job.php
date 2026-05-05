<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

use MeroKam\Core\Auth;
use MeroKam\Core\Response;
use MeroKam\Models\CompanyModel;
use MeroKam\Models\JobModel;

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::json(['ok' => false, 'error' => 'Method not allowed'], 405);
}

$user = Auth::requireRole('employer');
$co = CompanyModel::byUserId($user['id']);
if (!$co) {
    Response::json(['ok' => false, 'error' => 'No company'], 400);
}

$raw = json_decode(file_get_contents('php://input'), true) ?? [];
$id = (int) ($raw['id'] ?? $_GET['id'] ?? 0);
if ($id < 1) {
    Response::json(['ok' => false, 'error' => 'Invalid id'], 422);
}

$ok = JobModel::delete($id, (int) $co['id']);
Response::json(['ok' => $ok]);
