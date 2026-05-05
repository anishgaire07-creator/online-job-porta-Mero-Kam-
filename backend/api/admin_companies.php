<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

use MeroKam\Core\Auth;
use MeroKam\Core\Response;
use MeroKam\Models\CompanyModel;

Auth::requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    Response::json(['ok' => true, 'companies' => CompanyModel::listAll(500, 0)]);
}

if ($_SERVER['REQUEST_METHOD'] === 'PATCH') {
    $raw = json_decode(file_get_contents('php://input'), true) ?? [];
    $id = (int) ($raw['id'] ?? 0);
    if ($id < 1) {
        Response::json(['ok' => false, 'error' => 'Invalid id'], 422);
    }
    CompanyModel::update($id, ['verified' => !empty($raw['verified']) ? 1 : 0]);
    Response::json(['ok' => true]);
}

Response::json(['ok' => false, 'error' => 'Method not allowed'], 405);
