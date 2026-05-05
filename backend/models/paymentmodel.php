<?php

declare(strict_types=1);

namespace MeroKam\Models;

use MeroKam\Core\Database;

final class PaymentModel
{
    public static function plans(): array
    {
        return Database::pdo()->query('SELECT * FROM plans ORDER BY price ASC')->fetchAll();
    }

    public static function planBySlug(string $slug): ?array
    {
        $st = Database::pdo()->prepare('SELECT * FROM plans WHERE slug = ?');
        $st->execute([$slug]);
        $r = $st->fetch();
        return $r ?: null;
    }

    public static function createPayment(int $userId, int $planId, float $amount, string $status = 'pending', ?string $ref = null): int
    {
        $st = Database::pdo()->prepare(
            'INSERT INTO payments (user_id, plan_id, amount, status, reference) VALUES (?,?,?,?,?)'
        );
        $st->execute([$userId, $planId, $amount, $status, $ref]);
        return (int) Database::pdo()->lastInsertId();
    }

    public static function listForUser(int $userId): array
    {
        $st = Database::pdo()->prepare(
            'SELECT p.*, pl.name AS plan_name FROM payments p JOIN plans pl ON pl.id = p.plan_id WHERE p.user_id = ? ORDER BY p.id DESC'
        );
        $st->execute([$userId]);
        return $st->fetchAll();
    }

    public static function listAll(int $limit = 100): array
    {
        $limit = max(1, min(1000, $limit));
        $st = Database::pdo()->prepare(
            "SELECT p.*, u.name AS user_name, u.email, pl.name AS plan_name
             FROM payments p
             JOIN users u ON u.id = p.user_id
             JOIN plans pl ON pl.id = p.plan_id
             ORDER BY p.id DESC LIMIT {$limit}"
        );
        $st->execute();
        return $st->fetchAll();
    }

    public static function setStatus(int $paymentId, string $status): void
    {
        $st = Database::pdo()->prepare('UPDATE payments SET status = ? WHERE id = ?');
        $st->execute([$status, $paymentId]);
    }
}
