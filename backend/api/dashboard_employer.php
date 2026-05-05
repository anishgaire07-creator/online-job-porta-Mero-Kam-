<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

use MeroKam\Core\Auth;
use MeroKam\Core\Response;
use MeroKam\Models\CompanyModel;
use MeroKam\Models\EmployerCreditsModel;
use MeroKam\Models\JobModel;
use MeroKam\Models\MessageModel;

$user = Auth::requireRole('employer');
$co = CompanyModel::byUserId($user['id']);
$jobs = $co ? JobModel::forEmployer((int) $co['id']) : [];
$credits = EmployerCreditsModel::get($user['id']);
$msgs = MessageModel::inbox($user['id']);

Response::json([
    'ok' => true,
    'company' => $co,
    'jobs' => $jobs,
    'credits' => $credits,
    'messages_preview' => array_slice($msgs, 0, 20),
]);
