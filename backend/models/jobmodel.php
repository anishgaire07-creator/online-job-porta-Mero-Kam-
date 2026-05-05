<?php

declare(strict_types=1);

namespace MeroKam\Models;

use MeroKam\Core\Database;
use PDO;

final class JobModel
{
    /** @param array<string,mixed> $filters */
    public static function search(array $filters, int $limit, int $offset): array
    {
        $where = ['j.status = ?'];
        $params = ['approved'];

        if (!empty($filters['keyword'])) {
            $where[] = '(j.title LIKE ? OR j.description LIKE ?)';
            $kw = '%' . $filters['keyword'] . '%';
            $params[] = $kw;
            $params[] = $kw;
        }
        if (!empty($filters['location'])) {
            $where[] = 'j.location LIKE ?';
            $params[] = '%' . $filters['location'] . '%';
        }
        if (!empty($filters['type'])) {
            $where[] = 'j.type = ?';
            $params[] = $filters['type'];
        }
        if (!empty($filters['experience_level'])) {
            $where[] = 'j.experience_level = ?';
            $params[] = $filters['experience_level'];
        }
        if (isset($filters['salary_min'])) {
            $where[] = '(j.salary_max IS NULL OR j.salary_max >= ?)';
            $params[] = (int) $filters['salary_min'];
        }
        if (isset($filters['salary_max'])) {
            $where[] = '(j.salary_min IS NULL OR j.salary_min <= ?)';
            $params[] = (int) $filters['salary_max'];
        }

        $lim = max(1, min(50, (int) $limit));
        $off = max(0, (int) $offset);
        $sql = 'SELECT j.*, c.company_name, c.logo AS company_logo, c.location AS company_location
                FROM jobs j
                JOIN companies c ON c.id = j.company_id
                WHERE ' . implode(' AND ', $where) . '
                ORDER BY j.is_featured DESC, j.created_at DESC
                LIMIT ' . $lim . ' OFFSET ' . $off;

        $st = Database::pdo()->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }

    public static function countSearch(array $filters): int
    {
        $where = ['j.status = ?'];
        $params = ['approved'];

        if (!empty($filters['keyword'])) {
            $where[] = '(j.title LIKE ? OR j.description LIKE ?)';
            $kw = '%' . $filters['keyword'] . '%';
            $params[] = $kw;
            $params[] = $kw;
        }
        if (!empty($filters['location'])) {
            $where[] = 'j.location LIKE ?';
            $params[] = '%' . $filters['location'] . '%';
        }
        if (!empty($filters['type'])) {
            $where[] = 'j.type = ?';
            $params[] = $filters['type'];
        }
        if (!empty($filters['experience_level'])) {
            $where[] = 'j.experience_level = ?';
            $params[] = $filters['experience_level'];
        }
        if (isset($filters['salary_min'])) {
            $where[] = '(j.salary_max IS NULL OR j.salary_max >= ?)';
            $params[] = (int) $filters['salary_min'];
        }
        if (isset($filters['salary_max'])) {
            $where[] = '(j.salary_min IS NULL OR j.salary_min <= ?)';
            $params[] = (int) $filters['salary_max'];
        }

        $sql = 'SELECT COUNT(*) FROM jobs j JOIN companies c ON c.id = j.company_id WHERE ' . implode(' AND ', $where);
        $st = Database::pdo()->prepare($sql);
        $st->execute($params);
        return (int) $st->fetchColumn();
    }

    public static function find(int $id): ?array
    {
        $st = Database::pdo()->prepare(
            'SELECT j.*, c.company_name, c.logo AS company_logo, c.description AS company_description, c.website, c.user_id AS employer_user_id
             FROM jobs j JOIN companies c ON c.id = j.company_id WHERE j.id = ?'
        );
        $st->execute([$id]);
        $r = $st->fetch();
        return $r ?: null;
    }

    public static function create(array $row): int
    {
        $st = Database::pdo()->prepare(
            'INSERT INTO jobs (company_id, title, description, salary_min, salary_max, location, type, experience_level, status, is_featured)
             VALUES (?,?,?,?,?,?,?,?,?,?)'
        );
        $st->execute([
            $row['company_id'],
            $row['title'],
            $row['description'],
            $row['salary_min'],
            $row['salary_max'],
            $row['location'],
            $row['type'],
            $row['experience_level'],
            $row['status'],
            $row['is_featured'] ? 1 : 0,
        ]);
        return (int) Database::pdo()->lastInsertId();
    }

    public static function update(int $id, int $companyId, array $data): bool
    {
        $allowed = ['title', 'description', 'salary_min', 'salary_max', 'location', 'type', 'experience_level', 'is_featured'];
        $fields = [];
        $vals = [];
        foreach ($allowed as $k) {
            if (array_key_exists($k, $data)) {
                $fields[] = "$k = ?";
                $vals[] = $data[$k];
            }
        }
        if (!$fields) {
            return false;
        }
        $vals[] = $id;
        $vals[] = $companyId;
        $sql = 'UPDATE jobs SET ' . implode(', ', $fields) . ' WHERE id = ? AND company_id = ?';
        $st = Database::pdo()->prepare($sql);
        $st->execute($vals);
        return $st->rowCount() > 0;
    }

    public static function delete(int $id, int $companyId): bool
    {
        $st = Database::pdo()->prepare('DELETE FROM jobs WHERE id = ? AND company_id = ?');
        $st->execute([$id, $companyId]);
        return $st->rowCount() > 0;
    }

    public static function incrementViews(int $id): void
    {
        Database::pdo()->prepare('UPDATE jobs SET views_count = views_count + 1 WHERE id = ?')->execute([$id]);
    }

    public static function latest(int $limit = 8): array
    {
        $lim = max(1, min(50, (int) $limit));
        $sql = "SELECT j.*, c.company_name, c.logo AS company_logo
             FROM jobs j JOIN companies c ON c.id = j.company_id
             WHERE j.status = 'approved' ORDER BY j.created_at DESC LIMIT {$lim}";
        return Database::pdo()->query($sql)->fetchAll();
    }

    public static function featured(int $limit = 6): array
    {
        $lim = max(1, min(50, (int) $limit));
        $sql = "SELECT j.*, c.company_name, c.logo AS company_logo
             FROM jobs j JOIN companies c ON c.id = j.company_id
             WHERE j.status = 'approved' AND j.is_featured = 1
             ORDER BY j.created_at DESC LIMIT {$lim}";
        return Database::pdo()->query($sql)->fetchAll();
    }

    public static function forEmployer(int $companyId): array
    {
        $st = Database::pdo()->prepare(
            'SELECT * FROM jobs WHERE company_id = ? ORDER BY created_at DESC'
        );
        $st->execute([$companyId]);
        return $st->fetchAll();
    }

    public static function pending(int $limit = 100): array
    {
        $lim = max(1, min(500, (int) $limit));
        $sql = 'SELECT j.*, c.company_name FROM jobs j JOIN companies c ON c.id = j.company_id
                WHERE j.status = ? ORDER BY j.created_at ASC LIMIT ' . $lim;
        $st = Database::pdo()->prepare($sql);
        $st->execute(['pending']);
        return $st->fetchAll();
    }

    public static function allForAdmin(int $limit = 500): array
    {
        $lim = max(1, min(1000, (int) $limit));
        $sql = 'SELECT j.*, c.company_name FROM jobs j
                INNER JOIN companies c ON c.id = j.company_id
                ORDER BY j.id DESC LIMIT ' . $lim;
        return Database::pdo()->query($sql)->fetchAll();
    }

    public static function setStatus(int $id, string $status): void
    {
        $st = Database::pdo()->prepare('UPDATE jobs SET status = ? WHERE id = ?');
        $st->execute([$status, $id]);
    }

    public static function suggestions(string $q, int $limit = 8): array
    {
        if (strlen($q) < 2) {
            return [];
        }
        $kw = $q . '%';
        $lim = max(1, min(30, (int) $limit));
        $st = Database::pdo()->prepare(
            "SELECT DISTINCT j.title FROM jobs j WHERE j.status = 'approved' AND j.title LIKE ? LIMIT {$lim}"
        );
        $st->execute([$kw]);
        return array_column($st->fetchAll(), 'title');
    }

    public static function locationSuggestions(string $q, int $limit = 8): array
    {
        if (strlen($q) < 2) {
            return [];
        }
        $lim = max(1, min(30, (int) $limit));
        $st = Database::pdo()->prepare(
            "SELECT DISTINCT j.location FROM jobs j WHERE j.status = 'approved' AND j.location LIKE ? LIMIT {$lim}"
        );
        $st->execute(['%' . $q . '%']);
        return array_values(array_filter(array_column($st->fetchAll(), 'location')));
    }

    public static function recommendForSkills(array $skills, int $userId, int $limit = 10): array
    {
        if (!$skills) {
            return self::latest($limit);
        }
        $likes = [];
        $params = [];
        foreach ($skills as $s) {
            $likes[] = 'j.description LIKE ?';
            $params[] = '%' . $s . '%';
        }
        $placeholders = implode(' OR ', $likes);
        $params[] = $userId;
        $lim = max(1, min(50, (int) $limit));
        $sql = "SELECT j.*, c.company_name, c.logo AS company_logo
                FROM jobs j JOIN companies c ON c.id = j.company_id
                WHERE j.status = 'approved' AND ($placeholders)
                AND j.id NOT IN (SELECT job_id FROM applications WHERE user_id = ?)
                ORDER BY j.created_at DESC LIMIT {$lim}";
        $st = Database::pdo()->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }
}
