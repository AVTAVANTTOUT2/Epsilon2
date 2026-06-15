<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Badge
{
    public static function findAll(): array
    {
        return Database::fetchAll('SELECT * FROM badges ORDER BY badge_level ASC');
    }

    public static function findById(int $id): ?array
    {
        return Database::fetch('SELECT * FROM badges WHERE id = ?', [$id]);
    }

    public static function findByUser(int $userId): array
    {
        return Database::fetchAll(
            'SELECT b.*, ub.earned_at
             FROM badges b
             JOIN user_badges ub ON ub.badge_id = b.id
             WHERE ub.user_id = ?
             ORDER BY b.badge_level ASC',
            [$userId]
        );
    }

    public static function award(int $userId, int $badgeId): void
    {
        $existing = Database::fetch(
            'SELECT id FROM user_badges WHERE user_id = ? AND badge_id = ?',
            [$userId, $badgeId]
        );
        if ($existing === null) {
            Database::insert(
                'INSERT INTO user_badges (user_id, badge_id, earned_at) VALUES (?, ?, datetime("now"))',
                [$userId, $badgeId]
            );
        }
    }

    /**
     * Attribue automatiquement les badges selon la progression de l'utilisateur.
     */
    public static function autoAward(int $userId): void
    {
        $accessCode = \App\Models\User::getAccessCodeArray($userId);
        $maxLevel = max($accessCode);

        if ($maxLevel >= 2) {
            self::award($userId, 1); // Apprenti
        }
        if ($maxLevel >= 3) {
            self::award($userId, 2); // Compagnon
        }
        if ($maxLevel >= 4) {
            self::award($userId, 3); // Passeur
        }
        if ($maxLevel >= 5) {
            self::award($userId, 4); // Guide
        }
    }
}
