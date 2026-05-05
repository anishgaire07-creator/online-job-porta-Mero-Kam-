<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

use MeroKam\Core\Auth;
use MeroKam\Core\Response;
use MeroKam\Models\JobModel;
use MeroKam\Models\UserModel;

$user = Auth::requireRole('seeker');
$skills = UserModel::getSkills($user['id']);
$jobs = JobModel::recommendForSkills($skills, $user['id'], 15);

Response::json(['ok' => true, 'jobs' => $jobs]);
