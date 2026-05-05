<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

use MeroKam\Core\Auth;
use MeroKam\Core\Response;
use MeroKam\Models\ResumeModel;
use MeroKam\Models\UserModel;

$user = Auth::requireRole('seeker');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $r = ResumeModel::get($user['id']);
    $skills = UserModel::getSkills($user['id']);
    Response::json(['ok' => true, 'resume' => $r, 'skills' => $skills]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'PUT') {
    $raw = json_decode(file_get_contents('php://input'), true) ?? [];
    ResumeModel::upsert($user['id'], [
        'summary' => $raw['summary'] ?? '',
        'experience' => $raw['experience'] ?? '',
        'education' => $raw['education'] ?? '',
        'skills' => $raw['skills'] ?? '',
    ]);
    if (!empty($raw['skills_list']) && is_array($raw['skills_list'])) {
        UserModel::setSkills($user['id'], $raw['skills_list']);
    } elseif (!empty($raw['skills'])) {
        UserModel::setSkills($user['id'], array_map('trim', explode(',', $raw['skills'])));
    }
    Response::json(['ok' => true, 'resume' => ResumeModel::get($user['id']), 'skills' => UserModel::getSkills($user['id'])]);
}

Response::json(['ok' => false, 'error' => 'Method not allowed'], 405);
