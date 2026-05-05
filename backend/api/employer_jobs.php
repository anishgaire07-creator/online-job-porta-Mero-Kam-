<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

use MeroKam\Core\Auth;
use MeroKam\Core\Response;
use MeroKam\Models\CompanyModel;
use MeroKam\Models\EmployerCreditsModel;
use MeroKam\Models\JobModel;

$user = Auth::requireRole('employer');
$co = CompanyModel::byUserId($user['id']);
if (!$co) {
    Response::json(['ok' => true, 'jobs' => [], 'credits' => EmployerCreditsModel::get($user['id'])]);
}

$credits = EmployerCreditsModel::get($user['id']);
$jobs = JobModel::forEmployer((int) $co['id']);

Response::json(['ok' => true, 'jobs' => $jobs, 'credits' => $credits]);
