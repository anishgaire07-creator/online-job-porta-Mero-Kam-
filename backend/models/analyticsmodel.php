<?php

declare(strict_types=1);

namespace MeroKam\Models;

use MeroKam\Core\Database;

final class AnalyticsModel
{
    /** @return array<string,int|float> */
    public static function summary(): array
    {
        $pdo = Database::pdo();
        $users = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
        $jobs = (int) $pdo->query("SELECT COUNT(*) FROM jobs WHERE status = 'approved'")->fetchColumn();
        $pending = (int) $pdo->query("SELECT COUNT(*) FROM jobs WHERE status = 'pending'")->fetchColumn();
        $apps = (int) $pdo->query('SELECT COUNT(*) FROM applications')->fetchColumn();
        $companies = (int) $pdo->query('SELECT COUNT(*) FROM companies')->fetchColumn();
        $revenue = (float) $pdo->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status = 'completed'")->fetchColumn();

        try {
            $st = $pdo->query(
                "SELECT DATE_FORMAT(created_at, '%Y-%m') AS m, COUNT(*) AS c
                 FROM jobs WHERE status = 'approved'
                 GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                 ORDER BY m DESC LIMIT 12"
            );
            $jobsByMonth = $st ? $st->fetchAll(\PDO::FETCH_ASSOC) : [];
        } catch (\Throwable) {
            $jobsByMonth = [];
        }

        return [
            'users' => $users,
            'jobs' => $jobs,
            'pending_jobs' => $pending,
            'applications' => $apps,
            'companies' => $companies,
            'revenue' => $revenue,
            'jobs_by_month' => $jobsByMonth,
        ];
    }
}
