<?php

declare(strict_types=1);

namespace MeroKam\Models;

use MeroKam\Core\Database;

final class EmployerCreditsModel
{
    public static function ensureRow(int $userId): void
    {
        $st = Database::pdo()->prepare(
            'INSERT IGNORE INTO employer_credits (user_id, job_credits, featured_credits) VALUES (?, 3, 0)'
        );
        $st->execute([$userId]);
    }

    public static function get(int $userId): array
    {
        self::ensureRow($userId);
        $st = Database::pdo()->prepare('SELECT * FROM employer_credits WHERE user_id = ?');
        $st->execute([$userId]);
        return $st->fetch() ?: ['user_id' => $userId, 'job_credits' => 0, 'featured_credits' => 0];
    }

    public static function addCredits(int $userId, int $jobs, int $featured): void
    {
        self::ensureRow($userId);
        $st = Database::pdo()->prepare(
            'UPDATE employer_credits SET job_credits = job_credits + ?, featured_credits = featured_credits + ? WHERE user_id = ?'
        );
        $st->execute([$jobs, $featured, $userId]);
    }

    public static function consumeJobCredit(int $userId): bool
    {
        self::ensureRow($userId);
        $st = Database::pdo()->prepare(
            'UPDATE employer_credits SET job_credits = job_credits - 1 WHERE user_id = ? AND job_credits > 0'
        );
        $st->execute([$userId]);
        return $st->rowCount() > 0;
    }

    public static function consumeFeatured(int $userId): bool
    {
        self::ensureRow($userId);
        $st = Database::pdo()->prepare(
            'UPDATE employer_credits SET featured_credits = featured_credits - 1 WHERE user_id = ? AND featured_credits > 0'
        );
        $st->execute([$userId]);
        return $st->rowCount() > 0;
    }
}
