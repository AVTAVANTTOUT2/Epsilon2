<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Core\Session;
use App\Core\Uploader;
use App\Models\Course;
use App\Models\Challenge;
use App\Models\Submission;
use App\Models\User;
use App\Models\Badge;

final class SubmissionController
{
    public function show(string $courseId, string $challengeId): string
    {
        $user = Session::user();
        if (!$user) {
            redirect('/login');
        }

        $userId = (int)$user['id'];
        $course = Course::findById((int)$courseId);

        if (!$course) {
            http_response_code(404);
            return View::render('errors/404', ['title' => 'Parcours introuvable']);
        }

        $challenge = Challenge::findById((int)$challengeId);

        if (!$challenge || (int)$challenge['course_id'] !== (int)$courseId) {
            http_response_code(404);
            return View::render('errors/404', ['title' => 'Défi introuvable']);
        }

        $accessCodeArray = User::getAccessCodeArray($userId);
        $courseIndex = (int)$course['course_index'];

        if (!Course::isJoined($courseIndex, $accessCodeArray)) {
            flash('error', 'Vous devez d\'abord rejoindre ce parcours.');
            redirect('/courses/' . $courseId);
        }

        $existingSubmission = Challenge::getUserSubmission((int)$challengeId, $userId);
        $allSubmissions = Submission::findByChallenge((int)$challengeId);

        return View::render('submissions/upload', [
            'title' => $challenge['title'] . ' - Epsilon',
            'user' => $user,
            'course' => $course,
            'challenge' => $challenge,
            'existingSubmission' => $existingSubmission,
            'allSubmissions' => $allSubmissions,
        ]);
    }

    public function upload(string $courseId, string $challengeId): string
    {
        $user = Session::user();
        if (!$user) {
            redirect('/login');
        }

        $userId = (int)$user['id'];
        $course = Course::findById((int)$courseId);

        if (!$course) {
            flash('error', 'Parcours introuvable.');
            redirect('/courses');
        }

        $challenge = Challenge::findById((int)$challengeId);

        if (!$challenge || (int)$challenge['course_id'] !== (int)$courseId) {
            flash('error', 'Défi introuvable.');
            redirect('/courses/' . $courseId);
        }

        $accessCodeArray = User::getAccessCodeArray($userId);
        $courseIndex = (int)$course['course_index'];

        if (!Course::isJoined($courseIndex, $accessCodeArray)) {
            flash('error', 'Vous devez d\'abord rejoindre ce parcours.');
            redirect('/courses/' . $courseId);
        }

        if (!isset($_FILES['file']) || $_FILES['file']['error'] === UPLOAD_ERR_NO_FILE) {
            flash('error', 'Veuillez sélectionner un fichier.');
            redirect('/courses/' . $courseId . '/challenges/' . $challengeId);
        }

        $uploader = new Uploader();
        $result = $uploader->upload($_FILES['file'], $userId . '/' . $courseId . '-' . $challengeId);

        if ($result === false) {
            flash('error', $uploader->getLastError());
            redirect('/courses/' . $courseId . '/challenges/' . $challengeId);
        }

        $submissionId = Submission::create($userId, (int)$challengeId, $result);

        // Mettre à jour l'accessCode : passer au niveau du challenge réussi
        $newLevel = (int)$challenge['rank_level'];
        User::updateAccessCodeAtIndex($userId, $courseIndex, $newLevel);

        // Attribution automatique des badges
        Badge::autoAward($userId);

        flash('success', 'Votre travail a été soumis avec succès !');
        redirect('/courses/' . $courseId . '/challenges/' . $challengeId);

        return '';
    }

    public function list(): string
    {
        $user = Session::user();
        if (!$user) {
            redirect('/login');
        }

        $userId = (int)$user['id'];
        $submissions = Submission::findByUser($userId);

        return View::render('submissions/list', [
            'title' => 'Mes Soumissions - Epsilon',
            'user' => $user,
            'submissions' => $submissions,
        ]);
    }

    public function detail(string $id): string
    {
        $user = Session::user();
        if (!$user) {
            redirect('/login');
        }

        $submission = Submission::findById((int)$id);

        if (!$submission) {
            http_response_code(404);
            return View::render('errors/404', ['title' => 'Soumission introuvable']);
        }

        return View::render('submissions/detail', [
            'title' => 'Détail de la soumission - Epsilon',
            'user' => $user,
            'submission' => $submission,
        ]);
    }
}
