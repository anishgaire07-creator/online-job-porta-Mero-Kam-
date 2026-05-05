<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

use MeroKam\Core\Response;
use MeroKam\Models\AnalyticsModel;
use MeroKam\Models\CompanyModel;
use MeroKam\Models\JobModel;

$s = AnalyticsModel::summary();

Response::json([
    'ok' => true,
    'stats' => [
        'jobs' => $s['jobs'],
        'companies' => $s['companies'],
        'users' => $s['users'],
    ],
    'featured_jobs' => JobModel::featured(6),
    'latest_jobs' => JobModel::latest(8),
    'top_companies' => CompanyModel::listFeatured(8),
]);
