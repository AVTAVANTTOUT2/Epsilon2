<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Course
{
    public static function findAll(): array
    {
        return Database::fetchAll(
            'SELECT * FROM courses WHERE is_active = 1 ORDER BY course_index ASC'
        );
    }

    public static function findById(int $id): ?array
    {
        return Database::fetch('SELECT * FROM courses WHERE id = ?', [$id]);
    }

    public static function findByIndex(int $index): ?array
    {
        return Database::fetch('SELECT * FROM courses WHERE course_index = ?', [$index]);
    }

    /**
     * Vérifie si un utilisateur peut rejoindre un parcours selon les règles d'accès progressif.
     */
    public static function canJoin(int $courseIndex, array $accessCodeArray): bool
    {
        return match ($courseIndex) {
            0 => $accessCodeArray[0] === 0,
            1 => ($accessCodeArray[1] ?? 0) >= 2,
            2 => ($accessCodeArray[0] ?? 0) >= 2,
            3 => ($accessCodeArray[0] ?? 0) >= 2,
            default => false,
        };
    }

    /**
     * Vérifie si un utilisateur a déjà rejoint un parcours.
     */
    public static function isJoined(int $courseIndex, array $accessCodeArray): bool
    {
        return ($accessCodeArray[$courseIndex] ?? 0) >= 1;
    }

    /**
     * Récupère le prochain challenge à réaliser pour un parcours.
     */
    public static function getNextChallengeLevel(int $courseIndex, array $accessCodeArray): int
    {
        return ($accessCodeArray[$courseIndex] ?? 0) + 1;
    }

    /**
     * Récupère les challenges d'un parcours à un niveau donné.
     */
    public static function getChallengesByRank(int $courseId, int $rankLevel): array
    {
        return Database::fetchAll(
            'SELECT * FROM challenges WHERE course_id = ? AND rank_level = ? ORDER BY challenge_order ASC',
            [$courseId, $rankLevel]
        );
    }

    /**
     * Récupère tous les challenges d'un parcours.
     */
    public static function getAllChallenges(int $courseId): array
    {
        return Database::fetchAll(
            'SELECT * FROM challenges WHERE course_id = ? ORDER BY rank_level ASC, challenge_order ASC',
            [$courseId]
        );
    }

    /**
     * Retourne le nom du rang correspondant à un niveau.
     */
    public static function rankName(int $level): string
    {
        return match ($level) {
            0 => 'Non suivi',
            1 => 'Suivi',
            2 => 'Apprenti',
            3 => 'Compagnon',
            4 => 'Passeur',
            5 => 'Guide',
            default => 'Inconnu',
        };
    }

    /**
     * Retourne l'icône associée au rang.
     */
    public static function rankIcon(int $level): string
    {
        return match ($level) {
            0 => 'fa-circle',
            1 => 'fa-arrow-right',
            2 => 'fa-graduation-cap',
            3 => 'fa-handshake',
            4 => 'fa-hand-holding-heart',
            5 => 'fa-star',
            default => 'fa-circle',
        };
    }

    /**
     * Retourne la classe CSS associée au rang.
     */
    public static function rankClass(int $level): string
    {
        return match ($level) {
            0 => 'rank-none',
            1 => 'rank-following',
            2 => 'rank-apprentice',
            3 => 'rank-companion',
            4 => 'rank-passer',
            5 => 'rank-guide',
            default => 'rank-none',
        };
    }
}
