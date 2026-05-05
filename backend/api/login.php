<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

use MeroKam\Core\Auth;
use MeroKam\Core\Response;
use MeroKam\Models\UserModel;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::json(['ok' => false, 'error' => 'Method not allowed'], 405);
}

$raw = json_decode(file_get_contents('php://input'), true) ?? [];
$email = strtolower(trim((string) ($raw['email'] ?? '')));
$password = (string) ($raw['password'] ?? '');

$user = UserModel::findByEmail($email);
if (!$user || !password_verify($password, $user['password'])) {
    Response::json(['ok' => false, 'error' => 'Invalid credentials'], 401);
}

Auth::login((int) $user['id'], $user['name'], $user['email'], $user['role']);

Response::json([
    'ok' => true,
    'user' => [
        'id' => (int) $user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'role' => $user['role'],
    ],
]);
