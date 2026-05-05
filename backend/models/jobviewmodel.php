<?php

declare(strict_types=1);

namespace MeroKam\Models;

use MeroKam\Core\Database;

final class JobViewModel
{
    public static function record(?int $userId, int $jobId, ?string $sessionId): void
    {
        $st = Database::pdo()->prepare(
            'INSERT INTO job_views (user_id, job_id, session_id) VALUES (?,?,?)'
        );
        $st->execute([$userId, $jobId, $sessionId]);
    }

    public static function recentForUser(int $userId, int $limit = 10): array
    {
        $limit = max(1, min(100, $limit));
        $st = Database::pdo()->prepare(
            "SELECT j.id, j.title, j.location, MAX(v.viewed_at) AS viewed_at, c.company_name, c.logo AS company_logo
             FROM job_views v
             JOIN jobs j ON j.id = v.job_id
             JOIN companies c ON c.id = j.company_id
             WHERE v.user_id = ? AND j.status = ?
             GROUP BY j.id, j.title, j.location, c.company_name, c.logo
             ORDER BY viewed_at DESC
             LIMIT {$limit}"
        );
        $st->execute([$userId, 'approved']);
        return $st->fetchAll();
    }
}
