<?php

declare(strict_types=1);

namespace MeroKam\Models;

use MeroKam\Core\Database;

final class CompanyModel
{
    public static function byUserId(int $userId): ?array
    {
        $st = Database::pdo()->prepare('SELECT * FROM companies WHERE user_id = ? LIMIT 1');
        $st->execute([$userId]);
        $r = $st->fetch();
        return $r ?: null;
    }

    public static function byId(int $id): ?array
    {
        $st = Database::pdo()->prepare('SELECT c.*, u.name AS owner_name, u.email AS owner_email FROM companies c JOIN users u ON u.id = c.user_id WHERE c.id = ?');
        $st->execute([$id]);
        $r = $st->fetch();
        return $r ?: null;
    }

    public static function create(int $userId, string $name, ?string $desc, ?string $website, ?string $location): int
    {
        $st = Database::pdo()->prepare(
            'INSERT INTO companies (user_id, company_name, description, website, location) VALUES (?,?,?,?,?)'
        );
        $st->execute([$userId, $name, $desc, $website, $location]);
        return (int) Database::pdo()->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $allowed = ['company_name', 'description', 'website', 'location', 'logo', 'verified'];
        $fields = [];
        $vals = [];
        foreach ($allowed as $k) {
            if (array_key_exists($k, $data)) {
                $fields[] = "$k = ?";
                $vals[] = $data[$k];
            }
        }
        if (!$fields) {
            return;
        }
        $vals[] = $id;
        $sql = 'UPDATE companies SET ' . implode(', ', $fields) . ' WHERE id = ?';
        Database::pdo()->prepare($sql)->execute($vals);
    }

    public static function listFeatured(int $limit = 12): array
    {
        $limit = max(1, min(100, $limit));
        $sql = "SELECT c.id, c.company_name, c.logo, c.location, c.description,
                (SELECT COUNT(*) FROM jobs j WHERE j.company_id = c.id AND j.status = ?) AS job_count
                FROM companies c
                HAVING job_count > 0
                ORDER BY job_count DESC
                LIMIT {$limit}";
        $st = Database::pdo()->prepare($sql);
        $st->execute(['approved']);
        return $st->fetchAll();
    }

    public static function listAll(int $limit = 200, int $offset = 0): array
    {
        $limit = max(1, min(1000, $limit));
        $offset = max(0, $offset);
        $sql = "SELECT c.*, u.email FROM companies c JOIN users u ON u.id = c.user_id ORDER BY c.id DESC LIMIT {$limit} OFFSET {$offset}";
        $st = Database::pdo()->prepare($sql);
        $st->execute();
        return $st->fetchAll();
    }

    public static function count(): int
    {
        return (int) Database::pdo()->query('SELECT COUNT(*) FROM companies')->fetchColumn();
    }
}
