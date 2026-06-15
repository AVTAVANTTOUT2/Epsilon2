<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class User
{
    public static function findById(int $id): ?array
    {
        return Database::fetch('SELECT * FROM users WHERE id = ?', [$id]);
    }

    public static function findByEmail(string $email): ?array
    {
        return Database::fetch('SELECT * FROM users WHERE email = ?', [$email]);
    }

    public static function findByVerifyToken(string $token): ?array
    {
        return Database::fetch(
            'SELECT * FROM users WHERE email_verify_token = ? AND email_verified_at IS NULL',
            [$token]
        );
    }

    public static function findByPasswordResetToken(string $token): ?array
    {
        return Database::fetch(
            'SELECT * FROM users WHERE password_reset_token = ? AND password_reset_expires_at > datetime("now")',
            [$token]
        );
    }

    public static function findByRememberToken(string $token): ?array
    {
        return Database::fetch('SELECT * FROM users WHERE remember_token = ?', [$token]);
    }

    public static function create(array $data): int
    {
        $cost = max(10, (int)env('BCRYPT_COST', '12'));
        return (int)Database::insert(
            'INSERT INTO users (email, password, name, email_verify_token, created_at, updated_at)
             VALUES (?, ?, ?, ?, datetime("now"), datetime("now"))',
            [
                $data['email'],
                password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => $cost]),
                $data['name'] ?? '',
                $data['email_verify_token'] ?? null,
            ]
        );
    }

    public static function verifyEmail(int $userId): void
    {
        Database::execute(
            'UPDATE users SET email_verified_at = datetime("now"), email_verify_token = NULL, updated_at = datetime("now") WHERE id = ?',
            [$userId]
        );
    }

    public static function updatePassword(int $userId, string $password): void
    {
        $cost = max(10, (int)env('BCRYPT_COST', '12'));
        Database::execute(
            'UPDATE users SET password = ?, password_reset_token = NULL, password_reset_expires_at = NULL, updated_at = datetime("now") WHERE id = ?',
            [password_hash($password, PASSWORD_BCRYPT, ['cost' => $cost]), $userId]
        );
    }

    public static function updateRememberToken(int $userId, ?string $token): void
    {
        Database::execute(
            'UPDATE users SET remember_token = ?, updated_at = datetime("now") WHERE id = ?',
            [$token, $userId]
        );
    }

    public static function updateAccessCode(int $userId, string $accessCode): void
    {
        Database::execute(
            'UPDATE users SET access_code = ?, updated_at = datetime("now") WHERE id = ?',
            [$accessCode, $userId]
        );
    }

    public static function updateProfile(int $userId, string $name): void
    {
        Database::execute(
            'UPDATE users SET name = ?, updated_at = datetime("now") WHERE id = ?',
            [$name, $userId]
        );
    }

    public static function setPasswordResetToken(int $userId, string $token): void
    {
        Database::execute(
            'UPDATE users SET password_reset_token = ?, password_reset_expires_at = datetime("now", "+1 hour"), updated_at = datetime("now") WHERE id = ?',
            [$token, $userId]
        );
    }

    public static function getAccessCode(int $userId): string
    {
        $user = self::findById($userId);
        return $user['access_code'] ?? '0 0 0 0';
    }

    public static function getAccessCodeArray(int $userId): array
    {
        $code = self::getAccessCode($userId);
        return array_map('intval', explode(' ', $code));
    }

    public static function updateAccessCodeAtIndex(int $userId, int $index, int $value): void
    {
        $codes = self::getAccessCodeArray($userId);
        if (isset($codes[$index])) {
            $codes[$index] = max($codes[$index], $value);
            self::updateAccessCode($userId, implode(' ', $codes));
        }
    }
}
