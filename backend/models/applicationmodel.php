<?php

declare(strict_types=1);

namespace MeroKam\Models;

use MeroKam\Core\Database;

final class ApplicationModel
{
    public static function create(int $jobId, int $userId, ?string $cvPath, ?string $cover): void
    {
        $st = Database::pdo()->prepare(
            'INSERT INTO applications (job_id, user_id, cv_path, cover_letter) VALUES (?,?,?,?)
             ON DUPLICATE KEY UPDATE cv_path = VALUES(cv_path), cover_letter = VALUES(cover_letter), status = "pending"'
        );
        $st->execute([$jobId, $userId, $cvPath, $cover]);
    }

    public static function byUser(int $userId): array
    {
        $st = Database::pdo()->prepare(
            'SELECT a.*, j.title AS job_title, j.location AS job_location, c.company_name
             FROM applications a
             JOIN jobs j ON j.id = a.job_id
             JOIN companies c ON c.id = j.company_id
             WHERE a.user_id = ?
             ORDER BY a.created_at DESC'
        );
        $st->execute([$userId]);
        return $st->fetchAll();
    }

    public static function byJob(int $jobId, ?string $status = null): array
    {
        $sql = 'SELECT a.*, u.name AS applicant_name, u.email, u.phone, u.photo
                FROM applications a
                JOIN users u ON u.id = a.user_id
                WHERE a.job_id = ?';
        $p = [$jobId];
        if ($status) {
            $sql .= ' AND a.status = ?';
            $p[] = $status;
        }
        $sql .= ' ORDER BY a.created_at DESC';
        $st = Database::pdo()->prepare($sql);
        $st->execute($p);
        return $st->fetchAll();
    }

    public static function updateStatus(int $applicationId, int $employerUserId, string $status): bool
    {
        $st = Database::pdo()->prepare(
            'UPDATE applications a
             JOIN jobs j ON j.id = a.job_id
             JOIN companies c ON c.id = j.company_id
             SET a.status = ?
             WHERE a.id = ? AND c.user_id = ?'
        );
        $st->execute([$status, $applicationId, $employerUserId]);
        return $st->rowCount() > 0;
    }

    public static function hasApplied(int $userId, int $jobId): bool
    {
        $st = Database::pdo()->prepare('SELECT 1 FROM applications WHERE user_id = ? AND job_id = ?');
        $st->execute([$userId, $jobId]);
        return (bool) $st->fetchColumn();
    }
}
