<?php

declare(strict_types=1);

namespace App\Core;

final class Session
{
    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function isLoggedIn(): bool
    {
        return isset($_SESSION['user_id']);
    }

    public static function userId(): ?int
    {
        return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
    }

    public static function user(): ?array
    {
        if (!self::isLoggedIn()) {
            return null;
        }
        return \App\Models\User::findById(self::userId());
    }

    public static function login(int $userId, string $email, bool $remember = false): void
    {
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_email'] = $email;

        if ($remember) {
            $token = bin2hex(random_bytes(32));
            \App\Models\User::updateRememberToken($userId, $token);
            setcookie(
                'remember_token',
                $token,
                [
                    'expires' => time() + 86400 * 365,
                    'path' => '/',
                    'httponly' => true,
                    'samesite' => 'Lax',
                ]
            );
        }
    }

    public static function logout(): void
    {
        if (isset($_COOKIE['remember_token'])) {
            $userId = self::userId();
            if ($userId) {
                \App\Models\User::updateRememberToken($userId, null);
            }
            setcookie('remember_token', '', [
                'expires' => time() - 3600,
                'path' => '/',
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        session_destroy();
    }

    public static function autoLogin(): bool
    {
        if (self::isLoggedIn()) {
            return true;
        }

        $token = $_COOKIE['remember_token'] ?? null;
        if ($token) {
            $user = \App\Models\User::findByRememberToken($token);
            if ($user) {
                self::login((int)$user['id'], $user['email']);
                return true;
            }
        }

        return false;
    }
}
