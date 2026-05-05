<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

use MeroKam\Core\Auth;
use MeroKam\Core\Response;
use MeroKam\Models\ApplicationModel;
use MeroKam\Models\CompanyModel;
use MeroKam\Models\JobModel;
use MeroKam\Models\JobViewModel;
use MeroKam\Models\SavedJobModel;

$id = (int) ($_GET['id'] ?? 0);
if ($id < 1) {
    Response::json(['ok' => false, 'error' => 'Invalid id'], 422);
}

$job = JobModel::find($id);
if (!$job) {
    Response::json(['ok' => false, 'error' => 'Not found'], 404);
}

$u = Auth::user();
$canView = ($job['status'] === 'approved');
if (!$canView && $u) {
    if ($u['role'] === 'admin') {
        $canView = true;
    } elseif ($u['role'] === 'employer') {
        $co = CompanyModel::byUserId((int) $u['id']);
        if ($co && (int) $co['id'] === (int) $job['company_id']) {
            $canView = true;
        }
    }
}
if (!$canView) {
    Response::json(['ok' => false, 'error' => 'Not found'], 404);
}

if ($job['status'] === 'approved') {
    JobModel::incrementViews($id);
    $uid = $u['id'] ?? null;
    JobViewModel::record($uid ? (int) $uid : null, $id, session_id());
}

$extra = [];
if ($u && $u['role'] === 'seeker') {
    $extra['applied'] = ApplicationModel::hasApplied((int) $u['id'], $id);
    $extra['saved'] = SavedJobModel::isSaved((int) $u['id'], $id);
}

Response::json(['ok' => true, 'job' => $job] + $extra);
