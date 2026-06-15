<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Evaluation
{
    public static function findById(int $id): ?array
    {
        return Database::fetch(
            'SELECT e.*, u.name as evaluator_name
             FROM evaluations e
             JOIN users u ON u.id = e.evaluator_id
             WHERE e.id = ?',
            [$id]
        );
    }

    public static function findBySubmission(int $submissionId): array
    {
        return Database::fetchAll(
            'SELECT e.*, u.name as evaluator_name
             FROM evaluations e
             JOIN users u ON u.id = e.evaluator_id
             WHERE e.submission_id = ?
             ORDER BY e.created_at DESC',
            [$submissionId]
        );
    }

    public static function findByEvaluator(int $evaluatorId): array
    {
        return Database::fetchAll(
            'SELECT e.*, u.name as user_name, s.original_filename, c.title as challenge_title,
                    co.name as course_name
             FROM evaluations e
             JOIN submissions s ON s.id = e.submission_id
             JOIN users u ON u.id = s.user_id
             JOIN challenges c ON c.id = s.challenge_id
             JOIN courses co ON co.id = c.course_id
             WHERE e.evaluator_id = ?
             ORDER BY e.created_at DESC',
            [$evaluatorId]
        );
    }

    public static function findByUserSubmissions(int $userId): array
    {
        return Database::fetchAll(
            'SELECT e.*, u.name as evaluator_name,
                    s.original_filename, c.title as challenge_title, co.name as course_name
             FROM evaluations e
             JOIN submissions s ON s.id = e.submission_id
             JOIN users u ON u.id = e.evaluator_id
             JOIN challenges c ON c.id = s.challenge_id
             JOIN courses co ON co.id = c.course_id
             WHERE s.user_id = ?
             ORDER BY e.created_at DESC',
            [$userId]
        );
    }

    public static function getAverageScore(int $submissionId): ?float
    {
        $result = Database::fetch(
            'SELECT AVG(score) as avg_score, COUNT(*) as cnt FROM evaluations WHERE submission_id = ?',
            [$submissionId]
        );
        if ($result && $result['cnt'] > 0) {
            return round((float)$result['avg_score'], 1);
        }
        return null;
    }

    public static function create(int $submissionId, int $evaluatorId, int $score, string $comment): int
    {
        return (int)Database::insert(
            'INSERT INTO evaluations (submission_id, evaluator_id, score, comment, created_at)
             VALUES (?, ?, ?, ?, datetime("now"))',
            [$submissionId, $evaluatorId, $score, $comment]
        );
    }

    public static function countByEvaluator(int $evaluatorId): int
    {
        return (int)(Database::fetch(
            'SELECT COUNT(*) as cnt FROM evaluations WHERE evaluator_id = ?',
            [$evaluatorId]
        )['cnt'] ?? 0);
    }

    /**
     * Vérifie si l'utilisateur a déjà évalué cette soumission.
     */
    public static function hasEvaluated(int $submissionId, int $evaluatorId): bool
    {
        $result = Database::fetch(
            'SELECT COUNT(*) as cnt FROM evaluations WHERE submission_id = ? AND evaluator_id = ?',
            [$submissionId, $evaluatorId]
        );
        return ($result['cnt'] ?? 0) > 0;
    }
}
