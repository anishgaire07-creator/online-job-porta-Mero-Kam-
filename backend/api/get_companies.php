<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

use MeroKam\Core\Response;
use MeroKam\Models\CompanyModel;

$featured = isset($_GET['featured']) && $_GET['featured'] === '1';
if ($featured) {
    Response::json(['ok' => true, 'companies' => CompanyModel::listFeatured(20)]);
}

$list = CompanyModel::listAll(100, (int) ($_GET['offset'] ?? 0));
Response::json(['ok' => true, 'companies' => $list]);
