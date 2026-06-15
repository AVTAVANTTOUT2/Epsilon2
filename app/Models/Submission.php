<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Submission
{
    public static function findById(int $id): ?array
    {
        return Database::fetch(
            'SELECT s.*, u.name as user_name, u.email as user_email,
                    c.title as challenge_title, c.rank_level, co.name as course_name, co.id as course_id
             FROM submissions s
             JOIN users u ON u.id = s.user_id
             JOIN challenges c ON c.id = s.challenge_id
             JOIN courses co ON co.id = c.course_id
             WHERE s.id = ?',
            [$id]
        );
    }

    public static function findByUser(int $userId): array
    {
        return Database::fetchAll(
            'SELECT s.*, c.title as challenge_title, c.rank_level, co.name as course_name, co.color as course_color
             FROM submissions s
             JOIN challenges c ON c.id = s.challenge_id
             JOIN courses co ON co.id = c.course_id
             WHERE s.user_id = ?
             ORDER BY s.created_at DESC',
            [$userId]
        );
    }

    public static function findByChallenge(int $challengeId, ?int $excludeUserId = null): array
    {
        $sql = 'SELECT s.*, u.name as user_name, u.email as user_email
                FROM submissions s
                JOIN users u ON u.id = s.user_id
                WHERE s.challenge_id = ?';
        $params = [$challengeId];

        if ($excludeUserId !== null) {
            $sql .= ' AND s.user_id != ?';
            $params[] = $excludeUserId;
        }

        $sql .= ' ORDER BY s.created_at DESC';

        if ($excludeUserId === null) {
            return Database::fetchAll($sql, $params);
        }
        return Database::fetchAll($sql, $params);
    }

    /**
     * Récupère toutes les soumissions pour évaluation (hors celles de l'utilisateur courant).
     */
    public static function findForEvaluation(?int $excludeUserId = null, int $limit = 20): array
    {
        // Récupère les soumissions qui n'ont pas encore été évaluées par l'utilisateur
        if ($excludeUserId !== null) {
            return Database::fetchAll(
                'SELECT s.*, u.name as user_name, c.title as challenge_title, c.rank_level,
                        co.name as course_name, co.color as course_color,
                        (SELECT COUNT(*) FROM evaluations WHERE submission_id = s.id) as eval_count
                 FROM submissions s
                 JOIN users u ON u.id = s.user_id
                 JOIN challenges c ON c.id = s.challenge_id
                 JOIN courses co ON co.id = c.course_id
                 WHERE s.user_id != ?
                   AND s.id NOT IN (SELECT submission_id FROM evaluations WHERE evaluator_id = ?)
                 ORDER BY s.created_at DESC
                 LIMIT ?',
                [$excludeUserId, $excludeUserId, $limit]
            );
        }

        return Database::fetchAll(
            'SELECT s.*, u.name as user_name, c.title as challenge_title, c.rank_level,
                    co.name as course_name, co.color as course_color,
                    (SELECT COUNT(*) FROM evaluations WHERE submission_id = s.id) as eval_count
             FROM submissions s
             JOIN users u ON u.id = s.user_id
             JOIN challenges c ON c.id = s.challenge_id
             JOIN courses co ON co.id = c.course_id
             ORDER BY s.created_at DESC
             LIMIT ?',
            [$limit]
        );
    }

    public static function create(int $userId, int $challengeId, array $fileInfo): int
    {
        return (int)Database::insert(
            'INSERT INTO submissions (user_id, challenge_id, file_path, original_filename, file_type, file_size, status, created_at)
             VALUES (?, ?, ?, ?, ?, ?, "pending", datetime("now"))',
            [
                $userId,
                $challengeId,
                $fileInfo['path'],
                $fileInfo['original_name'],
                $fileInfo['type'],
                $fileInfo['size'],
            ]
        );
    }

    public static function updateStatus(int $id, string $status): void
    {
        Database::execute('UPDATE submissions SET status = ? WHERE id = ?', [$status, $id]);
    }

    public static function countByUser(int $userId): int
    {
        return (int)(Database::fetch(
            'SELECT COUNT(*) as cnt FROM submissions WHERE user_id = ?',
            [$userId]
        )['cnt'] ?? 0);
    }
}
