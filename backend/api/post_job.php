<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

use MeroKam\Core\Auth;
use MeroKam\Core\Response;
use MeroKam\Models\CompanyModel;
use MeroKam\Models\EmployerCreditsModel;
use MeroKam\Models\JobModel;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::json(['ok' => false, 'error' => 'Method not allowed'], 405);
}

$user = Auth::requireRole('employer');
$co = CompanyModel::byUserId($user['id']);
if (!$co) {
    Response::json(['ok' => false, 'error' => 'Create your company profile first'], 400);
}

$raw = json_decode(file_get_contents('php://input'), true) ?? [];
$title = trim((string) ($raw['title'] ?? ''));
$desc = trim((string) ($raw['description'] ?? ''));
if ($title === '' || $desc === '') {
    Response::json(['ok' => false, 'error' => 'Title and description required'], 422);
}

$featured = !empty($raw['is_featured']);
if ($featured && !EmployerCreditsModel::consumeFeatured($user['id'])) {
    Response::json(['ok' => false, 'error' => 'No featured credits available'], 402);
}
if (!EmployerCreditsModel::consumeJobCredit($user['id'])) {
    Response::json(['ok' => false, 'error' => 'No job post credits — purchase a plan'], 402);
}

$jid = JobModel::create([
    'company_id' => (int) $co['id'],
    'title' => $title,
    'description' => $desc,
    'salary_min' => isset($raw['salary_min']) ? (int) $raw['salary_min'] : null,
    'salary_max' => isset($raw['salary_max']) ? (int) $raw['salary_max'] : null,
    'location' => $raw['location'] ?? null,
    'type' => $raw['type'] ?? 'full-time',
    'experience_level' => $raw['experience_level'] ?? 'mid',
    'status' => 'pending',
    'is_featured' => $featured,
]);

Response::json(['ok' => true, 'job_id' => $jid]);
