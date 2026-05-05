<?php

declare(strict_types=1);

namespace MeroKam\Models;

use MeroKam\Core\Database;
use PDO;

final class UserModel
{
    public static function findByEmail(string $email): ?array
    {
        $st = Database::pdo()->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $st->execute([$email]);
        $r = $st->fetch();
        return $r ?: null;
    }

    public static function findById(int $id): ?array
    {
        $st = Database::pdo()->prepare('SELECT id, name, email, role, phone, photo, language_pref, created_at FROM users WHERE id = ?');
        $st->execute([$id]);
        $r = $st->fetch();
        return $r ?: null;
    }

    public static function create(string $name, string $email, string $hash, string $role, ?string $phone = null): int
    {
        $st = Database::pdo()->prepare(
            'INSERT INTO users (name, email, password, role, phone) VALUES (?,?,?,?,?)'
        );
        $st->execute([$name, $email, $hash, $role, $phone]);
        return (int) Database::pdo()->lastInsertId();
    }

    public static function updateProfile(int $id, array $data): void
    {
        $fields = [];
        $vals = [];
        foreach (['name', 'phone', 'photo', 'language_pref'] as $k) {
            if (array_key_exists($k, $data)) {
                $fields[] = "$k = ?";
                $vals[] = $data[$k];
            }
        }
        if (!$fields) {
            return;
        }
        $vals[] = $id;
        $sql = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = ?';
        $st = Database::pdo()->prepare($sql);
        $st->execute($vals);
    }

    public static function listAll(int $limit = 100, int $offset = 0, ?string $role = null): array
    {
        $sql = 'SELECT id, name, email, role, phone, created_at FROM users';
        $p = [];
        if ($role) {
            $sql .= ' WHERE role = ?';
            $p[] = $role;
        }
        $limit = max(1, min(1000, $limit));
        $offset = max(0, $offset);
        $sql .= " ORDER BY id DESC LIMIT {$limit} OFFSET {$offset}";
        $st = Database::pdo()->prepare($sql);
        $st->execute($p);
        return $st->fetchAll();
    }

    public static function count(?string $role = null): int
    {
        $sql = 'SELECT COUNT(*) FROM users';
        $p = [];
        if ($role) {
            $sql .= ' WHERE role = ?';
            $p[] = $role;
        }
        $st = Database::pdo()->prepare($sql);
        $st->execute($p);
        return (int) $st->fetchColumn();
    }

    public static function delete(int $id): void
    {
        $st = Database::pdo()->prepare('DELETE FROM users WHERE id = ? AND role != ?');
        $st->execute([$id, 'admin']);
    }

    public static function setSkills(int $userId, array $skills): void
    {
        $pdo = Database::pdo();
        $pdo->prepare('DELETE FROM user_skills WHERE user_id = ?')->execute([$userId]);
        $st = $pdo->prepare('INSERT INTO user_skills (user_id, skill) VALUES (?,?)');
        foreach (array_slice(array_unique(array_filter($skills)), 0, 30) as $s) {
            $st->execute([$userId, mb_substr($s, 0, 100)]);
        }
    }

    public static function getSkills(int $userId): array
    {
        $st = Database::pdo()->prepare('SELECT skill FROM user_skills WHERE user_id = ?');
        $st->execute([$userId]);
        return array_column($st->fetchAll(), 'skill');
    }
}
