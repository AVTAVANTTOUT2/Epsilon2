<?php

declare(strict_types=1);

session_start();

$rootDir = dirname(__DIR__);

require_once $rootDir . '/app/Helpers/functions.php';
require_once $rootDir . '/app/Core/Database.php';
require_once $rootDir . '/app/Core/Router.php';
require_once $rootDir . '/app/Core/View.php';
require_once $rootDir . '/app/Core/Session.php';
require_once $rootDir . '/app/Core/Validator.php';
require_once $rootDir . '/app/Core/Uploader.php';
require_once $rootDir . '/app/Middleware/AuthMiddleware.php';
require_once $rootDir . '/app/Models/User.php';
require_once $rootDir . '/app/Models/Course.php';
require_once $rootDir . '/app/Models/Challenge.php';
require_once $rootDir . '/app/Models/Submission.php';
require_once $rootDir . '/app/Models/Evaluation.php';
require_once $rootDir . '/app/Models/Badge.php';
require_once $rootDir . '/app/Controllers/AuthController.php';
require_once $rootDir . '/app/Controllers/CourseController.php';
require_once $rootDir . '/app/Controllers/SubmissionController.php';
require_once $rootDir . '/app/Controllers/EvaluationController.php';

use App\Core\Router;
use App\Controllers\AuthController;
use App\Controllers\CourseController;
use App\Controllers\SubmissionController;
use App\Controllers\EvaluationController;

$router = new Router();

// Routes publiques
$router->get('/', function () {
    if (App\Core\Session::isLoggedIn()) {
        header('Location: /dashboard');
        exit;
    }
    require_once dirname(__DIR__) . '/views/home.php';
});

$router->get('/login', [AuthController::class, 'loginForm']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/register', [AuthController::class, 'registerForm']);
$router->post('/register', [AuthController::class, 'register']);
$router->get('/verify-email', [AuthController::class, 'verifyEmail']);
$router->get('/logout', [AuthController::class, 'logout']);
$router->get('/password-reset', [AuthController::class, 'passwordResetForm']);
$router->post('/password-reset', [AuthController::class, 'passwordReset']);
$router->post('/password-reset/request', [AuthController::class, 'passwordResetRequest']);

// Routes authentifiées
$router->get('/dashboard', [CourseController::class, 'dashboard'], ['auth']);
$router->get('/courses', [CourseController::class, 'index'], ['auth']);
$router->get('/courses/{id}', [CourseController::class, 'show'], ['auth']);
$router->post('/courses/{id}/join', [CourseController::class, 'join'], ['auth']);
$router->get('/courses/{courseId}/challenges/{challengeId}', [SubmissionController::class, 'show'], ['auth']);
$router->post('/courses/{courseId}/challenges/{challengeId}/upload', [SubmissionController::class, 'upload'], ['auth']);
$router->get('/submissions', [SubmissionController::class, 'list'], ['auth']);
$router->get('/submissions/{id}', [SubmissionController::class, 'detail'], ['auth']);
$router->get('/evaluate', [EvaluationController::class, 'index'], ['auth']);
$router->post('/evaluate', [EvaluationController::class, 'store'], ['auth']);
$router->get('/evaluations', [EvaluationController::class, 'myEvaluations'], ['auth']);
$router->get('/profile', [AuthController::class, 'profile'], ['auth']);
$router->post('/profile', [AuthController::class, 'updateProfile'], ['auth']);

$router->dispatch();
