<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

use MeroKam\Core\Auth;
use MeroKam\Core\Response;
use MeroKam\Models\JobAlertModel;

$user = Auth::requireRole('seeker');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    Response::json(['ok' => true, 'alerts' => JobAlertModel::list($user['id'])]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw = json_decode(file_get_contents('php://input'), true) ?? [];
    $kw = trim((string) ($raw['keywords'] ?? ''));
    if ($kw === '') {
        Response::json(['ok' => false, 'error' => 'Keywords required'], 422);
    }
    $id = JobAlertModel::create($user['id'], $kw, $raw['location'] ?? null, isset($raw['salary_min']) ? (int) $raw['salary_min'] : null);
    Response::json(['ok' => true, 'id' => $id]);
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $raw = json_decode(file_get_contents('php://input'), true) ?? [];
    $id = (int) ($raw['id'] ?? $_GET['id'] ?? 0);
    if ($id < 1) {
        Response::json(['ok' => false, 'error' => 'Invalid id'], 422);
    }
    JobAlertModel::delete($id, $user['id']);
    Response::json(['ok' => true]);
}

if ($_SERVER['REQUEST_METHOD'] === 'PATCH') {
    $raw = json_decode(file_get_contents('php://input'), true) ?? [];
    $id = (int) ($raw['id'] ?? 0);
    JobAlertModel::toggle($id, $user['id'], (bool) ($raw['is_active'] ?? true));
    Response::json(['ok' => true]);
}

Response::json(['ok' => false, 'error' => 'Method not allowed'], 405);
