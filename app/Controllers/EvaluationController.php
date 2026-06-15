<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Core\Session;
use App\Core\Validator;
use App\Models\Submission;
use App\Models\Evaluation;
use App\Models\Badge;

final class EvaluationController
{
    public function index(): string
    {
        $user = Session::user();
        if (!$user) {
            redirect('/login');
        }

        $userId = (int)$user['id'];
        $submissions = Submission::findForEvaluation($userId);

        return View::render('evaluations/index', [
            'title' => 'Évaluer des travaux - Epsilon',
            'user' => $user,
            'submissions' => $submissions,
        ]);
    }

    public function store(): string
    {
        $user = Session::user();
        if (!$user) {
            redirect('/login');
        }

        $userId = (int)$user['id'];

        try {
            $validator = new Validator($_POST);
            $validator
                ->required('submission_id', 'score')
                ->integer('submission_id')
                ->integer('score')
                ->range('score', 1, 5);

            if (!$validator->passes()) {
                flash('error', 'Veuillez sélectionner une note valide (1-5).');
                redirect('/evaluate');
            }

            $submissionId = (int)$_POST['submission_id'];
            $score = (int)$_POST['score'];
            $comment = trim($_POST['comment'] ?? '');

            $submission = Submission::findById($submissionId);

            if (!$submission) {
                flash('error', 'Soumission introuvable.');
                redirect('/evaluate');
            }

            if ((int)$submission['user_id'] === $userId) {
                flash('error', 'Vous ne pouvez pas évaluer votre propre travail.');
                redirect('/evaluate');
            }

            if (Evaluation::hasEvaluated($submissionId, $userId)) {
                flash('error', 'Vous avez déjà évalué cette soumission.');
                redirect('/evaluate');
            }

            Evaluation::create($submissionId, $userId, $score, $comment);

            // Mise à jour du statut de la soumission
            Submission::updateStatus($submissionId, 'reviewed');

            // Attribution badge "Passeur" si >= 3 évaluations
            $evalCount = Evaluation::countByEvaluator($userId);
            if ($evalCount >= 3) {
                Badge::award($userId, 3); // Passeur
            }

            flash('success', 'Évaluation enregistrée. Merci pour votre contribution !');
            redirect('/evaluate');

        } catch (\Exception $e) {
            flash('error', 'Erreur lors de l\'évaluation.');
            redirect('/evaluate');
        }

        return '';
    }

    public function myEvaluations(): string
    {
        $user = Session::user();
        if (!$user) {
            redirect('/login');
        }

        $userId = (int)$user['id'];
        $evaluationsGiven = Evaluation::findByEvaluator($userId);
        $evaluationsReceived = Evaluation::findByUserSubmissions($userId);

        return View::render('evaluations/my', [
            'title' => 'Mes Évaluations - Epsilon',
            'user' => $user,
            'evaluationsGiven' => $evaluationsGiven,
            'evaluationsReceived' => $evaluationsReceived,
        ]);
    }
}
