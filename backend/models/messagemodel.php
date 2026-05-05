<?php

declare(strict_types=1);

namespace MeroKam\Models;

use MeroKam\Core\Database;

final class MessageModel
{
    public static function send(int $from, int $to, ?int $jobId, string $body): int
    {
        $st = Database::pdo()->prepare(
            'INSERT INTO messages (from_user_id, to_user_id, job_id, body) VALUES (?,?,?,?)'
        );
        $st->execute([$from, $to, $jobId, $body]);
        return (int) Database::pdo()->lastInsertId();
    }

    public static function inbox(int $userId): array
    {
        $st = Database::pdo()->prepare(
            'SELECT m.*, u.name AS other_name,
                    (m.from_user_id = ?) AS sent_by_me
             FROM messages m
             JOIN users u ON u.id = IF(m.from_user_id = ?, m.to_user_id, m.from_user_id)
             WHERE m.to_user_id = ? OR m.from_user_id = ?
             ORDER BY m.created_at DESC
             LIMIT 200'
        );
        $st->execute([$userId, $userId, $userId, $userId]);
        return $st->fetchAll();
    }

    public static function conversation(int $userId, int $otherId): array
    {
        $st = Database::pdo()->prepare(
            'SELECT m.*, uf.name AS from_name
             FROM messages m
             JOIN users uf ON uf.id = m.from_user_id
             WHERE (m.from_user_id = ? AND m.to_user_id = ?) OR (m.from_user_id = ? AND m.to_user_id = ?)
             ORDER BY m.created_at ASC
             LIMIT 500'
        );
        $st->execute([$userId, $otherId, $otherId, $userId]);
        return $st->fetchAll();
    }

    public static function markRead(int $userId, int $fromUserId): void
    {
        $st = Database::pdo()->prepare(
            'UPDATE messages SET is_read = 1 WHERE to_user_id = ? AND from_user_id = ?'
        );
        $st->execute([$userId, $fromUserId]);
    }

    public static function unreadCount(int $userId): int
    {
        $st = Database::pdo()->prepare(
            'SELECT COUNT(*) FROM messages WHERE to_user_id = ? AND is_read = 0'
        );
        $st->execute([$userId]);
        return (int) $st->fetchColumn();
    }
}
