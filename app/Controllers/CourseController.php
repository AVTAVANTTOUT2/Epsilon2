<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Core\Session;
use App\Models\Course;
use App\Models\Challenge;
use App\Models\User;
use App\Models\Badge;
use App\Models\Submission;
use App\Models\Evaluation;

final class CourseController
{
    public function dashboard(): string
    {
        $user = Session::user();
        if (!$user) {
            redirect('/login');
        }

        $userId = (int)$user['id'];
        $accessCodeArray = User::getAccessCodeArray($userId);
        $courses = Course::findAll();
        $badges = Badge::findByUser($userId);
        $allBadges = Badge::findAll();
        $submissions = Submission::findByUser($userId);
        $evaluationsReceived = Evaluation::findByUserSubmissions($userId);
        $evaluationsGiven = Evaluation::findByEvaluator($userId);

        return View::render('dashboard', [
            'title' => 'Tableau de bord - Epsilon',
            'user' => $user,
            'accessCodeArray' => $accessCodeArray,
            'courses' => $courses,
            'badges' => $badges,
            'allBadges' => $allBadges,
            'submissions' => $submissions,
            'evaluationsReceived' => $evaluationsReceived,
            'evaluationsGiven' => $evaluationsGiven,
        ]);
    }

    public function index(): string
    {
        $user = Session::user();
        if (!$user) {
            redirect('/login');
        }

        $userId = (int)$user['id'];
        $accessCodeArray = User::getAccessCodeArray($userId);
        $courses = Course::findAll();

        return View::render('courses/index', [
            'title' => 'Parcours - Epsilon',
            'user' => $user,
            'accessCodeArray' => $accessCodeArray,
            'courses' => $courses,
        ]);
    }

    public function show(string $id): string
    {
        $user = Session::user();
        if (!$user) {
            redirect('/login');
        }

        $userId = (int)$user['id'];
        $courseId = (int)$id;
        $course = Course::findById($courseId);

        if (!$course) {
            http_response_code(404);
            return View::render('errors/404', ['title' => 'Parcours introuvable']);
        }

        $accessCodeArray = User::getAccessCodeArray($userId);
        $courseIndex = (int)$course['course_index'];
        $isJoined = Course::isJoined($courseIndex, $accessCodeArray);
        $canJoin = Course::canJoin($courseIndex, $accessCodeArray);
        $challenges = Course::getAllChallenges($courseId);
        $currentLevel = $accessCodeArray[$courseIndex] ?? 0;

        return View::render('courses/show', [
            'title' => $course['name'] . ' - Epsilon',
            'user' => $user,
            'course' => $course,
            'courseIndex' => $courseIndex,
            'isJoined' => $isJoined,
            'canJoin' => $canJoin,
            'challenges' => $challenges,
            'currentLevel' => $currentLevel,
            'accessCodeArray' => $accessCodeArray,
        ]);
    }

    public function join(string $id): string
    {
        $user = Session::user();
        if (!$user) {
            redirect('/login');
        }

        $userId = (int)$user['id'];
        $courseId = (int)$id;
        $course = Course::findById($courseId);

        if (!$course) {
            flash('error', 'Parcours introuvable.');
            redirect('/courses');
        }

        $accessCodeArray = User::getAccessCodeArray($userId);
        $courseIndex = (int)$course['course_index'];

        if (!Course::canJoin($courseIndex, $accessCodeArray)) {
            flash('error', 'Vous ne pouvez pas rejoindre ce parcours pour le moment.');
            redirect('/courses');
        }

        // Marquer comme "suivi" (niveau 1)
        User::updateAccessCodeAtIndex($userId, $courseIndex, 1);
        flash('success', 'Vous avez rejoint le parcours "' . e($course['name']) . '" !');
        redirect('/courses/' . $courseId);

        return '';
    }
}
