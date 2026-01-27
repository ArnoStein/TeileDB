<?php
declare(strict_types=1);

namespace App\Auth;

class Auth
{
    public static function check(): bool
    {
        return isset($_SESSION['user_id']) && is_int($_SESSION['user_id']) && $_SESSION['user_id'] > 0;
    }

    public static function requireLogin(): void
    {
        if (self::check()) {
            return;
        }
        header('Location: index.php?page=login');
        exit;
    }

    public static function login(int $userId): void
    {
        $_SESSION['user_id'] = $userId;
        session_regenerate_id(true);
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        // Also invalidate session cookie if exists
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
    }
}
