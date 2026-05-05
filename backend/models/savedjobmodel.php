<?php

declare(strict_types=1);

namespace MeroKam\Models;

use MeroKam\Core\Database;

final class SavedJobModel
{
    public static function save(int $userId, int $jobId): void
    {
        $st = Database::pdo()->prepare(
            'INSERT IGNORE INTO saved_jobs (user_id, job_id) VALUES (?,?)'
        );
        $st->execute([$userId, $jobId]);
    }

    public static function unsave(int $userId, int $jobId): void
    {
        $st = Database::pdo()->prepare('DELETE FROM saved_jobs WHERE user_id = ? AND job_id = ?');
        $st->execute([$userId, $jobId]);
    }

    public static function list(int $userId): array
    {
        $st = Database::pdo()->prepare(
            'SELECT s.*, j.title, j.location, j.salary_min, j.salary_max, j.type, c.company_name, c.logo AS company_logo
             FROM saved_jobs s
             JOIN jobs j ON j.id = s.job_id
             JOIN companies c ON c.id = j.company_id
             WHERE s.user_id = ?
             ORDER BY s.created_at DESC'
        );
        $st->execute([$userId]);
        return $st->fetchAll();
    }

    public static function isSaved(int $userId, int $jobId): bool
    {
        $st = Database::pdo()->prepare('SELECT 1 FROM saved_jobs WHERE user_id = ? AND job_id = ?');
        $st->execute([$userId, $jobId]);
        return (bool) $st->fetchColumn();
    }
}
