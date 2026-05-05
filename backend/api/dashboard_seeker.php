<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

use MeroKam\Core\Auth;
use MeroKam\Core\Response;
use MeroKam\Models\ApplicationModel;
use MeroKam\Models\JobAlertModel;
use MeroKam\Models\JobModel;
use MeroKam\Models\JobViewModel;
use MeroKam\Models\SavedJobModel;
use MeroKam\Models\UserModel;

$user = Auth::requireRole('seeker');

$skills = UserModel::getSkills($user['id']);
$rec = JobModel::recommendForSkills($skills, $user['id'], 10);

Response::json([
    'ok' => true,
    'applications' => ApplicationModel::byUser($user['id']),
    'saved_jobs' => SavedJobModel::list($user['id']),
    'job_alerts' => JobAlertModel::list($user['id']),
    'recent_jobs' => JobViewModel::recentForUser($user['id'], 10),
    'recommendations' => $rec,
]);
