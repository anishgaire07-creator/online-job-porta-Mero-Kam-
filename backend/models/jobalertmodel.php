<?php

declare(strict_types=1);

namespace MeroKam\Models;

use MeroKam\Core\Database;

final class JobAlertModel
{
    public static function list(int $userId): array
    {
        $st = Database::pdo()->prepare('SELECT * FROM job_alerts WHERE user_id = ? ORDER BY id DESC');
        $st->execute([$userId]);
        return $st->fetchAll();
    }

    public static function create(int $userId, string $keywords, ?string $location, ?int $salaryMin): int
    {
        $st = Database::pdo()->prepare(
            'INSERT INTO job_alerts (user_id, keywords, location, salary_min) VALUES (?,?,?,?)'
        );
        $st->execute([$userId, $keywords, $location, $salaryMin]);
        return (int) Database::pdo()->lastInsertId();
    }

    public static function delete(int $id, int $userId): void
    {
        $st = Database::pdo()->prepare('DELETE FROM job_alerts WHERE id = ? AND user_id = ?');
        $st->execute([$id, $userId]);
    }

    public static function toggle(int $id, int $userId, bool $active): void
    {
        $st = Database::pdo()->prepare('UPDATE job_alerts SET is_active = ? WHERE id = ? AND user_id = ?');
        $st->execute([$active ? 1 : 0, $id, $userId]);
    }
}
