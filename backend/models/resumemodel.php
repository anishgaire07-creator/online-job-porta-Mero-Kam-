<?php

declare(strict_types=1);

namespace MeroKam\Models;

use MeroKam\Core\Database;

final class ResumeModel
{
    public static function get(int $userId): ?array
    {
        $st = Database::pdo()->prepare('SELECT * FROM resume_data WHERE user_id = ?');
        $st->execute([$userId]);
        $r = $st->fetch();
        return $r ?: null;
    }

    public static function upsert(int $userId, array $data): void
    {
        $row = self::get($userId);
        if ($row) {
            $st = Database::pdo()->prepare(
                'UPDATE resume_data SET summary = ?, experience = ?, education = ?, skills = ? WHERE user_id = ?'
            );
            $st->execute([
                $data['summary'] ?? '',
                $data['experience'] ?? '',
                $data['education'] ?? '',
                $data['skills'] ?? '',
                $userId,
            ]);
        } else {
            $st = Database::pdo()->prepare(
                'INSERT INTO resume_data (user_id, summary, experience, education, skills) VALUES (?,?,?,?,?)'
            );
            $st->execute([
                $userId,
                $data['summary'] ?? '',
                $data['experience'] ?? '',
                $data['education'] ?? '',
                $data['skills'] ?? '',
            ]);
        }
    }
}
