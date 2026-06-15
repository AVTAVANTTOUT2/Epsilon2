<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Challenge
{
    public static function findById(int $id): ?array
    {
        return Database::fetch(
            'SELECT c.*, co.name as course_name, co.course_index FROM challenges c
             JOIN courses co ON co.id = c.course_id
             WHERE c.id = ?',
            [$id]
        );
    }

    public static function findByCourseAndRank(int $courseId, int $rankLevel): array
    {
        return Database::fetchAll(
            'SELECT * FROM challenges WHERE course_id = ? AND rank_level = ? ORDER BY challenge_order ASC',
            [$courseId, $rankLevel]
        );
    }

    public static function findAllByCourse(int $courseId): array
    {
        return Database::fetchAll(
            'SELECT * FROM challenges WHERE course_id = ? ORDER BY rank_level ASC, challenge_order ASC',
            [$courseId]
        );
    }

    /**
     * Récupère la soumission d'un utilisateur pour un challenge donné.
     */
    public static function getUserSubmission(int $challengeId, int $userId): ?array
    {
        return Database::fetch(
            'SELECT * FROM submissions WHERE challenge_id = ? AND user_id = ? ORDER BY created_at DESC LIMIT 1',
            [$challengeId, $userId]
        );
    }
}
