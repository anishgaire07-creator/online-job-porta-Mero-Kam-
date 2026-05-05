<?php

declare(strict_types=1);

namespace MeroKam\Core;

final class Auth
{
    public static function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'lifetime' => 86400 * 7,
                'path' => '/',
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            session_start();
        }
    }

    /** @return array{id:int,name:string,email:string,role:string}|null */
    public static function user(): ?array
    {
        self::startSession();
        if (empty($_SESSION['user_id'])) {
            return null;
        }
        return [
            'id' => (int) $_SESSION['user_id'],
            'name' => (string) ($_SESSION['user_name'] ?? ''),
            'email' => (string) ($_SESSION['user_email'] ?? ''),
            'role' => (string) ($_SESSION['user_role'] ?? 'seeker'),
        ];
    }

    public static function login(int $id, string $name, string $email, string $role): void
    {
        self::startSession();
        $_SESSION['user_id'] = $id;
        $_SESSION['user_name'] = $name;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_role'] = $role;
        session_regenerate_id(true);
    }

    public static function logout(): void
    {
        self::startSession();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }

    public static function requireRole(string ...$roles): array
    {
        $u = self::user();
        if (!$u) {
            Response::json(['ok' => false, 'error' => 'Unauthorized'], 401);
        }
        if ($roles && !in_array($u['role'], $roles, true)) {
            Response::json(['ok' => false, 'error' => 'Forbidden'], 403);
        }
        return $u;
    }

    public static function requireAdmin(): array
    {
        return self::requireRole('admin');
    }
}
