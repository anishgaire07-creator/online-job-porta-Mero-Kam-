<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

use MeroKam\Core\Auth;
use MeroKam\Core\Response;
use MeroKam\Models\ApplicationModel;
use MeroKam\Models\CompanyModel;
use MeroKam\Models\JobModel;

$user = Auth::requireRole('employer');
$co = CompanyModel::byUserId($user['id']);
if (!$co) {
    Response::json(['ok' => false, 'error' => 'No company'], 400);
}

$jobId = (int) ($_GET['job_id'] ?? 0);
if ($jobId < 1) {
    Response::json(['ok' => false, 'error' => 'job_id required'], 422);
}

$job = JobModel::find($jobId);
if (!$job || (int) $job['company_id'] !== (int) $co['id']) {
    Response::json(['ok' => false, 'error' => 'Not found'], 404);
}

$status = $_GET['status'] ?? '';
$status = $status !== '' ? $status : null;
$list = ApplicationModel::byJob($jobId, $status);

Response::json(['ok' => true, 'applicants' => $list]);
