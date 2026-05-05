<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

use MeroKam\Core\Auth;
use MeroKam\Core\Response;
use MeroKam\Helpers\Mail;
use MeroKam\Models\ApplicationModel;
use MeroKam\Models\JobModel;
use MeroKam\Models\UserModel;
use MeroKam\Helpers\Upload;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::json(['ok' => false, 'error' => 'Method not allowed'], 405);
}

$user = Auth::requireRole('seeker');
$jobId = (int) ($_POST['job_id'] ?? 0);
if ($jobId < 1) {
    Response::json(['ok' => false, 'error' => 'Invalid job'], 422);
}

$job = JobModel::find($jobId);
if (!$job || $job['status'] !== 'approved') {
    Response::json(['ok' => false, 'error' => 'Job not available'], 404);
}

$cover = trim((string) ($_POST['cover_letter'] ?? ''));
$cv = $_FILES['cv'] ?? null;
$cvPath = null;
if ($cv && ($cv['error'] ?? 0) !== UPLOAD_ERR_NO_FILE) {
    $up = Upload::cv($cv);
    if (!$up['ok']) {
        Response::json(['ok' => false, 'error' => $up['error'] ?? 'CV upload failed'], 400);
    }
    $cvPath = $up['path'] ?? null;
}

ApplicationModel::create($jobId, $user['id'], $cvPath, $cover ?: null);

$cfg = require dirname(__DIR__) . '/config/mail.php';
$seeker = UserModel::findById($user['id']);
Mail::notifyApplicationToAdmin(
    $cfg['admin_email'],
    $seeker['name'] ?? $user['name'],
    $seeker['email'] ?? $user['email'],
    $seeker['phone'] ?? null,
    $job['title'],
    $job['company_name'],
    $cover ?: null
);
Mail::notifyApplicant($seeker['email'] ?? $user['email'], $job['title'], $job['company_name']);

Response::json(['ok' => true]);
