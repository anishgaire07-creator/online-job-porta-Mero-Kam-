<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

use MeroKam\Core\Response;
use MeroKam\Models\JobModel;

$q = trim((string) ($_GET['q'] ?? ''));
$type = $_GET['type'] ?? 'title';

if ($type === 'location') {
    Response::json(['ok' => true, 'suggestions' => JobModel::locationSuggestions($q, 10)]);
}

Response::json(['ok' => true, 'suggestions' => JobModel::suggestions($q, 10)]);
