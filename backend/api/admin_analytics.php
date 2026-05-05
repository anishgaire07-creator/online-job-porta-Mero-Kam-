<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

use MeroKam\Core\Auth;
use MeroKam\Core\Response;
use MeroKam\Models\AnalyticsModel;

Auth::requireAdmin();
$data = AnalyticsModel::summary();

Response::json(['ok' => true, 'analytics' => $data]);
