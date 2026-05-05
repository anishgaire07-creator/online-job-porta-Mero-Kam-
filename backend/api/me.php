<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

use MeroKam\Core\Auth;
use MeroKam\Core\Response;
use MeroKam\Models\CompanyModel;
use MeroKam\Models\UserModel;

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $u = Auth::user();
    if (!$u) {
        Response::json(['ok' => true, 'user' => null]);
    }
    $full = UserModel::findById($u['id']);
    $company = $u['role'] === 'employer' ? CompanyModel::byUserId($u['id']) : null;
    Response::json(['ok' => true, 'user' => $full, 'company' => $company]);
}

if ($_SERVER['REQUEST_METHOD'] === 'PATCH') {
    $u = Auth::requireRole('seeker', 'employer', 'admin');
    $raw = json_decode(file_get_contents('php://input'), true) ?? [];
    $data = [];
    foreach (['name', 'phone', 'language_pref'] as $k) {
        if (array_key_exists($k, $raw)) {
            $data[$k] = $raw[$k];
        }
    }
    if ($data) {
        UserModel::updateProfile($u['id'], $data);
    }
    $full = UserModel::findById($u['id']);
    Response::json(['ok' => true, 'user' => $full]);
}

Response::json(['ok' => false, 'error' => 'Method not allowed'], 405);
