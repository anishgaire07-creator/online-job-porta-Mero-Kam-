<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

use MeroKam\Core\Auth;
use MeroKam\Core\Response;
use MeroKam\Models\UserModel;

Auth::requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $role = $_GET['role'] ?? null;
    $users = UserModel::listAll(200, 0, $role ?: null);
    Response::json(['ok' => true, 'users' => $users]);
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $raw = json_decode(file_get_contents('php://input'), true) ?? [];
    $id = (int) ($raw['id'] ?? 0);
    if ($id < 1) {
        Response::json(['ok' => false, 'error' => 'Invalid id'], 422);
    }
    UserModel::delete($id);
    Response::json(['ok' => true]);
}

Response::json(['ok' => false, 'error' => 'Method not allowed'], 405);
