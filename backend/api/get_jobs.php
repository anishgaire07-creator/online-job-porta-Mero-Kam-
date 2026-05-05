<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

use MeroKam\Core\Response;
use MeroKam\Models\JobModel;

$page = max(1, (int) ($_GET['page'] ?? 1));
$per = min(50, max(1, (int) ($_GET['per'] ?? 12)));
$offset = ($page - 1) * $per;

$filters = [
    'keyword' => $_GET['q'] ?? '',
    'location' => $_GET['location'] ?? '',
    'type' => $_GET['type'] ?? '',
    'experience_level' => $_GET['experience_level'] ?? '',
];
if (isset($_GET['salary_min']) && $_GET['salary_min'] !== '') {
    $filters['salary_min'] = (int) $_GET['salary_min'];
}
if (isset($_GET['salary_max']) && $_GET['salary_max'] !== '') {
    $filters['salary_max'] = (int) $_GET['salary_max'];
}

$jobs = JobModel::search($filters, $per, $offset);
$total = JobModel::countSearch($filters);

Response::json([
    'ok' => true,
    'jobs' => $jobs,
    'total' => $total,
    'page' => $page,
    'per' => $per,
]);
