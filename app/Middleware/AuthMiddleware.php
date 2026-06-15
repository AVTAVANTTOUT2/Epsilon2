<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Session;

final class AuthMiddleware
{
    public static function handle(): void
    {
        Session::autoLogin();

        if (!Session::isLoggedIn()) {
            $_SESSION['_intended_url'] = $_SERVER['REQUEST_URI'];
            header('Location: /login');
            exit;
        }
    }

    public static function guest(): void
    {
        Session::autoLogin();

        if (Session::isLoggedIn()) {
            header('Location: /dashboard');
            exit;
        }
    }
}
