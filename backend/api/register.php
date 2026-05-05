<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

use MeroKam\Core\Auth;
use MeroKam\Core\Response;
use MeroKam\Models\EmployerCreditsModel;
use MeroKam\Models\UserModel;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::json(['ok' => false, 'error' => 'Method not allowed'], 405);
}

$raw = json_decode(file_get_contents('php://input'), true) ?? [];
$name = trim((string) ($raw['name'] ?? ''));
$email = strtolower(trim((string) ($raw['email'] ?? '')));
$password = (string) ($raw['password'] ?? '');
$role = $raw['role'] ?? 'seeker';

if ($name === '' || $email === '' || strlen($password) < 8) {
    Response::json(['ok' => false, 'error' => 'Invalid input'], 422);
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    Response::json(['ok' => false, 'error' => 'Invalid email'], 422);
}
if (!in_array($role, ['seeker', 'employer'], true)) {
    $role = 'seeker';
}

if (UserModel::findByEmail($email)) {
    Response::json(['ok' => false, 'error' => 'Email already registered'], 409);
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$id = UserModel::create($name, $email, $hash, $role, $raw['phone'] ?? null);
if ($role === 'employer') {
    EmployerCreditsModel::ensureRow($id);
}
Auth::login($id, $name, $email, $role);

Response::json([
    'ok' => true,
    'user' => ['id' => $id, 'name' => $name, 'email' => $email, 'role' => $role],
]);
