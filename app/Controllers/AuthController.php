<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Core\Session;
use App\Core\Validator;
use App\Core\ValidationException;
use App\Models\User;
use App\Models\Badge;
use App\Models\Course;
use App\Models\Submission;
use App\Models\Evaluation;

final class AuthController
{
    public function loginForm(): string
    {
        if (Session::isLoggedIn()) {
            redirect('/dashboard');
        }
        return View::render('auth/login', ['title' => 'Connexion - Epsilon']);
    }

    public function login(): string
    {
        try {
            $validator = new Validator($_POST);
            $validator
                ->required('email', 'password')
                ->email('email');

            if (!$validator->passes()) {
                $_SESSION['_old'] = $_POST;
                flash('error', 'Veuillez corriger les erreurs ci-dessous.');
                redirect('/login');
            }

            $user = User::findByEmail($_POST['email']);

            if (!$user) {
                flash('error', 'Aucun compte trouvé avec cet email.');
                $_SESSION['_old'] = $_POST;
                redirect('/login');
            }

            if (!password_verify($_POST['password'], $user['password'])) {
                flash('error', 'Mot de passe incorrect.');
                $_SESSION['_old'] = $_POST;
                redirect('/login');
            }

            if ($user['email_verified_at'] === null) {
                flash('error', 'Veuillez vérifier votre email avant de vous connecter. Vérifiez vos spams.');
                redirect('/login');
            }

            $remember = isset($_POST['remember']);
            Session::login((int)$user['id'], $user['email'], $remember);

            $intendedUrl = $_SESSION['_intended_url'] ?? '/dashboard';
            unset($_SESSION['_intended_url']);
            redirect($intendedUrl);

        } catch (\Exception $e) {
            flash('error', 'Une erreur est survenue lors de la connexion.');
            redirect('/login');
        }

        return '';
    }

    public function registerForm(): string
    {
        if (Session::isLoggedIn()) {
            redirect('/dashboard');
        }
        return View::render('auth/register', ['title' => 'Inscription - Epsilon']);
    }

    public function register(): string
    {
        try {
            $validator = new Validator($_POST);
            $validator
                ->required('email', 'password', 'password_confirmation')
                ->email('email')
                ->minLength('password', 8)
                ->matches('password', 'password_confirmation', 'confirmation');

            if (!$validator->passes()) {
                $_SESSION['_old'] = $_POST;
                flash('error', 'Veuillez corriger les erreurs ci-dessous.');
                redirect('/register');
            }

            $existing = User::findByEmail($_POST['email']);
            if ($existing) {
                flash('error', 'Un compte existe déjà avec cet email.');
                $_SESSION['_old'] = $_POST;
                redirect('/register');
            }

            $token = bin2hex(random_bytes(32));

            User::create([
                'email' => $_POST['email'],
                'password' => $_POST['password'],
                'name' => $_POST['name'] ?? '',
                'email_verify_token' => $token,
            ]);

            // Envoi de l'email de vérification
            $verifyUrl = env('APP_URL') . '/verify-email?token=' . $token;
            $mailSent = $this->sendVerificationEmail($_POST['email'], $verifyUrl);

            if ($mailSent) {
                flash('success', 'Un email de vérification vous a été envoyé. Cliquez sur le lien pour activer votre compte.');
            } else {
                // En mode dev, on affiche le lien directement
                flash('success', 'Compte créé. <a href="' . e($verifyUrl) . '">Cliquez ici pour vérifier votre email</a> (mode développement).');
            }

            redirect('/login');

        } catch (\Exception $e) {
            flash('error', 'Une erreur est survenue lors de l\'inscription.');
            redirect('/register');
        }

        return '';
    }

    public function verifyEmail(): string
    {
        $token = $_GET['token'] ?? '';

        if ($token === '') {
            flash('error', 'Token de vérification invalide.');
            redirect('/login');
        }

        $user = User::findByVerifyToken($token);

        if (!$user) {
            flash('error', 'Token de vérification invalide ou expiré.');
            redirect('/login');
        }

        User::verifyEmail((int)$user['id']);
        flash('success', 'Email vérifié avec succès ! Vous pouvez maintenant vous connecter.');
        redirect('/login');

        return '';
    }

    public function logout(): string
    {
        Session::logout();
        redirect('/');
    }

    public function passwordResetForm(): string
    {
        return View::render('auth/password-reset', ['title' => 'Mot de passe oublié - Epsilon']);
    }

    public function passwordResetRequest(): string
    {
        $email = $_POST['email'] ?? '';

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('error', 'Veuillez fournir un email valide.');
            redirect('/password-reset');
        }

        $user = User::findByEmail($email);

        if ($user) {
            $token = bin2hex(random_bytes(32));
            User::setPasswordResetToken((int)$user['id'], $token);

            $resetUrl = env('APP_URL') . '/password-reset?token=' . $token;
            $mailSent = $this->sendResetEmail($email, $resetUrl);

            if (!$mailSent) {
                // Mode dev : afficher le lien
                flash('success', 'Lien de réinitialisation (mode dev) : <a href="' . e($resetUrl) . '">Réinitialiser</a>');
                redirect('/login');
            }
        }

        flash('success', 'Si un compte existe avec cet email, un lien de réinitialisation a été envoyé.');
        redirect('/login');

        return '';
    }

    public function passwordReset(): string
    {
        $token = $_GET['token'] ?? '';

        if ($token === '') {
            flash('error', 'Token de réinitialisation invalide.');
            redirect('/login');
        }

        $user = User::findByPasswordResetToken($token);

        if (!$user) {
            flash('error', 'Token de réinitialisation invalide ou expiré.');
            redirect('/login');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            return View::render('auth/password-reset-form', [
                'title' => 'Nouveau mot de passe - Epsilon',
                'token' => $token,
            ]);
        }

        // POST : mise à jour du mot de passe
        $validator = new Validator($_POST);
        $validator
            ->required('password', 'password_confirmation')
            ->minLength('password', 8)
            ->matches('password', 'password_confirmation', 'confirmation');

        if (!$validator->passes()) {
            $_SESSION['_old'] = $_POST;
            flash('error', 'Veuillez corriger les erreurs.');
            redirect('/password-reset?token=' . urlencode($token));
        }

        User::updatePassword((int)$user['id'], $_POST['password']);
        flash('success', 'Mot de passe mis à jour. Vous pouvez vous connecter.');
        redirect('/login');

        return '';
    }

    public function profile(): string
    {
        $user = Session::user();
        if (!$user) {
            redirect('/login');
        }

        $accessCodeArray = \App\Models\User::getAccessCodeArray((int)$user['id']);
        $courses = \App\Models\Course::findAll();
        $badges = \App\Models\Badge::findByUser((int)$user['id']);
        $allBadges = \App\Models\Badge::findAll();
        $submissionCount = \App\Models\Submission::countByUser((int)$user['id']);
        $evaluationCount = \App\Models\Evaluation::countByEvaluator((int)$user['id']);

        return View::render('auth/profile', [
            'title' => 'Mon Profil - Epsilon',
            'user' => $user,
            'accessCodeArray' => $accessCodeArray,
            'courses' => $courses,
            'badges' => $badges,
            'allBadges' => $allBadges,
            'submissionCount' => $submissionCount,
            'evaluationCount' => $evaluationCount,
        ]);
    }

    public function updateProfile(): string
    {
        $user = Session::user();
        if (!$user) {
            redirect('/login');
        }

        $name = trim($_POST['name'] ?? '');

        if ($name === '') {
            flash('error', 'Le nom ne peut pas être vide.');
            redirect('/profile');
        }

        User::updateProfile((int)$user['id'], $name);
        flash('success', 'Profil mis à jour.');
        redirect('/profile');

        return '';
    }

    private function sendVerificationEmail(string $to, string $verifyUrl): bool
    {
        $subject = 'Epsilon - Vérification de votre email';
        $message = $this->emailTemplate('Vérification de votre compte', '
            <p>Bonjour et bienvenue sur Epsilon !</p>
            <p>Pour activer votre compte, veuillez cliquer sur le lien ci-dessous :</p>
            <p><a href="' . e($verifyUrl) . '" style="display:inline-block;background:#6366f1;color:#fff;padding:12px 24px;text-decoration:none;border-radius:8px;">Vérifier mon email</a></p>
            <p>Si le bouton ne fonctionne pas, copiez ce lien :<br>' . e($verifyUrl) . '</p>
        ');

        return $this->sendMail($to, $subject, $message);
    }

    private function sendResetEmail(string $to, string $resetUrl): bool
    {
        $subject = 'Epsilon - Réinitialisation de votre mot de passe';
        $message = $this->emailTemplate('Réinitialisation du mot de passe', '
            <p>Vous avez demandé la réinitialisation de votre mot de passe.</p>
            <p>Cliquez sur le lien ci-dessous pour choisir un nouveau mot de passe :</p>
            <p><a href="' . e($resetUrl) . '" style="display:inline-block;background:#6366f1;color:#fff;padding:12px 24px;text-decoration:none;border-radius:8px;">Réinitialiser</a></p>
            <p>Ce lien expire dans 1 heure.</p>
        ');

        return $this->sendMail($to, $subject, $message);
    }

    private function emailTemplate(string $title, string $body): string
    {
        return '<!DOCTYPE html><html><head><meta charset="utf-8"></head><body style="font-family:sans-serif;background:#0f0f1a;color:#e2e8f0;padding:40px;">'
            . '<div style="max-width:600px;margin:0 auto;background:#1a1a2e;border-radius:16px;padding:40px;border:1px solid #2d2d4a;">'
            . '<h1 style="color:#6366f1;margin-bottom:8px;">Epsilon</h1>'
            . '<h2 style="color:#e2e8f0;margin-top:0;">' . e($title) . '</h2>'
            . $body
            . '<hr style="border-color:#2d2d4a;margin:30px 0;">'
            . '<p style="color:#64748b;font-size:12px;">Epsilon - Plateforme de peer-learning de l\'EPSI Lille</p>'
            . '</div></body></html>';
    }

    private function sendMail(string $to, string $subject, string $message): bool
    {
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=utf-8',
            'From: ' . env('MAIL_FROM_NAME', 'Epsilon') . ' <' . env('MAIL_FROM_ADDRESS', 'noreply@epsilon.local') . '>',
        ];

        return @mail($to, $subject, $message, implode("\r\n", $headers));
    }
}
