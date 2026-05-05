<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

use MeroKam\Core\Auth;
use MeroKam\Core\Response;
use MeroKam\Models\SavedJobModel;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::json(['ok' => false, 'error' => 'Method not allowed'], 405);
}

$user = Auth::requireRole('seeker');
$raw = json_decode(file_get_contents('php://input'), true) ?? [];
$jobId = (int) ($raw['job_id'] ?? 0);
if ($jobId < 1) {
    Response::json(['ok' => false, 'error' => 'Invalid job'], 422);
}

$action = $raw['action'] ?? 'save';
if ($action === 'unsave') {
    SavedJobModel::unsave($user['id'], $jobId);
} else {
    SavedJobModel::save($user['id'], $jobId);
}

Response::json(['ok' => true]);
