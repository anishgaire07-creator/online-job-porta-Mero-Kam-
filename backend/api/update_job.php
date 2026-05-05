<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

use MeroKam\Core\Auth;
use MeroKam\Core\Response;
use MeroKam\Models\CompanyModel;
use MeroKam\Models\JobModel;

if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'PATCH') {
    Response::json(['ok' => false, 'error' => 'Method not allowed'], 405);
}

$user = Auth::requireRole('employer');
$co = CompanyModel::byUserId($user['id']);
if (!$co) {
    Response::json(['ok' => false, 'error' => 'No company'], 400);
}

$raw = json_decode(file_get_contents('php://input'), true) ?? [];
$id = (int) ($raw['id'] ?? 0);
if ($id < 1) {
    Response::json(['ok' => false, 'error' => 'Invalid id'], 422);
}

$data = [];
foreach (['title', 'description', 'location', 'type', 'experience_level', 'salary_min', 'salary_max'] as $k) {
    if (array_key_exists($k, $raw)) {
        $data[$k] = $raw[$k];
    }
}
if (array_key_exists('is_featured', $raw)) {
    $data['is_featured'] = (int) (bool) $raw['is_featured'];
}

$ok = JobModel::update($id, (int) $co['id'], $data);
Response::json(['ok' => $ok]);
