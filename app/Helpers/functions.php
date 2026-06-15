<?php

declare(strict_types=1);

function env(string $key, string $default = ''): string
{
    static $env = null;
    if ($env === null) {
        $envFile = dirname(__DIR__, 2) . '/.env';
        $env = [];
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '' || str_starts_with($line, '#')) {
                    continue;
                }
                $parts = explode('=', $line, 2);
                if (count($parts) === 2) {
                    $env[trim($parts[0])] = trim($parts[1]);
                }
            }
        }
    }
    // Check getenv/putenv overrides (for tests)
    $override = getenv($key);
    if ($override !== false && $override !== '') {
        return $override;
    }
    return $env[$key] ?? $default;
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $url, int $statusCode = 302): never
{
    header('Location: ' . $url, true, $statusCode);
    exit;
}

function old(string $key, string $default = ''): string
{
    return e($_SESSION['_old'][$key] ?? $default);
}

function flash(string $key, ?string $value = null): ?string
{
    if ($value !== null) {
        $_SESSION['_flash'][$key] = $value;
        return null;
    }
    $val = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);
    return $val;
}

function asset(string $path): string
{
    return env('APP_URL') . '/' . ltrim($path, '/');
}

function route(string $name, array $params = []): string
{
    $routes = [
        'home' => '/',
        'login' => '/login',
        'register' => '/register',
        'dashboard' => '/dashboard',
        'courses' => '/courses',
        'profile' => '/profile',
    ];
    $url = $routes[$name] ?? '/';
    foreach ($params as $key => $value) {
        $url = str_replace('{' . $key . '}', (string)$value, $url);
    }
    return $url;
}

function csrf_token(): string
{
    if (!isset($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf_token" value="' . csrf_token() . '">';
}

function verify_csrf(): bool
{
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        return true;
    }
    $token = $_POST['_csrf_token'] ?? '';
    if (!isset($_SESSION['_csrf_token']) || !hash_equals($_SESSION['_csrf_token'], $token)) {
        return false;
    }
    unset($_SESSION['_csrf_token']);
    return true;
}

function slugify(string $text): string
{
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    return strtolower($text) ?: 'n-a';
}
