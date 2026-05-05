<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

use MeroKam\Core\Auth;
use MeroKam\Core\Response;
use MeroKam\Models\JobModel;

Auth::requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Default: all jobs (new posts are visible). Use filter=pending for moderation queue only.
    $filter = $_GET['filter'] ?? $_GET['status'] ?? 'all';
    if ($filter === 'pending') {
        Response::json(['ok' => true, 'jobs' => JobModel::pending(200)]);
    }
    Response::json(['ok' => true, 'jobs' => JobModel::allForAdmin(500)]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw = json_decode(file_get_contents('php://input'), true) ?? [];
    $id = (int) ($raw['id'] ?? 0);
    $status = $raw['status'] ?? '';
    if ($id < 1 || !in_array($status, ['approved', 'rejected', 'pending'], true)) {
        Response::json(['ok' => false, 'error' => 'Invalid input'], 422);
    }
    JobModel::setStatus($id, $status);
    Response::json(['ok' => true]);
}

Response::json(['ok' => false, 'error' => 'Method not allowed'], 405);
