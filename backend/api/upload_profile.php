<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

use MeroKam\Core\Auth;
use MeroKam\Core\Response;
use MeroKam\Helpers\Upload;
use MeroKam\Models\CompanyModel;
use MeroKam\Models\UserModel;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::json(['ok' => false, 'error' => 'Method not allowed'], 405);
}

$user = Auth::requireRole('seeker', 'employer');
$type = $_POST['type'] ?? 'photo';

if ($type === 'company_logo') {
    Auth::requireRole('employer');
    $up = Upload::image($_FILES['file'] ?? [], 'logos');
    if (!$up['ok']) {
        Response::json(['ok' => false, 'error' => $up['error'] ?? 'Upload failed'], 400);
    }
    $co = CompanyModel::byUserId($user['id']);
    if ($co && !empty($up['path'])) {
        CompanyModel::update((int) $co['id'], ['logo' => $up['path']]);
    }
    Response::json(['ok' => true, 'path' => $up['path']]);
}

$up = Upload::image($_FILES['file'] ?? [], 'photos');
if (!$up['ok']) {
    Response::json(['ok' => false, 'error' => $up['error'] ?? 'Upload failed'], 400);
}
if (!empty($up['path'])) {
    UserModel::updateProfile($user['id'], ['photo' => $up['path']]);
}
Response::json(['ok' => true, 'path' => $up['path']]);
