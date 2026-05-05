<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

use MeroKam\Core\Auth;
use MeroKam\Core\Response;
use MeroKam\Models\MessageModel;

$user = Auth::requireRole('seeker', 'employer');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $other = (int) ($_GET['with'] ?? 0);
    if ($other > 0) {
        MessageModel::markRead($user['id'], $other);
        Response::json(['ok' => true, 'messages' => MessageModel::conversation($user['id'], $other)]);
    }
    Response::json(['ok' => true, 'inbox' => MessageModel::inbox($user['id']), 'unread' => MessageModel::unreadCount($user['id'])]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw = json_decode(file_get_contents('php://input'), true) ?? [];
    $to = (int) ($raw['to_user_id'] ?? 0);
    $body = trim((string) ($raw['body'] ?? ''));
    $jobId = isset($raw['job_id']) ? (int) $raw['job_id'] : null;
    if ($to < 1 || $body === '') {
        Response::json(['ok' => false, 'error' => 'Invalid message'], 422);
    }
    if ($to === $user['id']) {
        Response::json(['ok' => false, 'error' => 'Cannot message yourself'], 400);
    }
    $id = MessageModel::send($user['id'], $to, $jobId, $body);
    Response::json(['ok' => true, 'message_id' => $id]);
}

Response::json(['ok' => false, 'error' => 'Method not allowed'], 405);
