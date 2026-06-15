<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    private array $routes = [];
    private array $patterns = [
        '{id}' => '(\d+)',
        '{courseId}' => '(\d+)',
        '{challengeId}' => '(\d+)',
        '{slug}' => '([a-z0-9-]+)',
    ];

    public function get(string $uri, callable|array $handler, array $middleware = []): self
    {
        return $this->addRoute('GET', $uri, $handler, $middleware);
    }

    public function post(string $uri, callable|array $handler, array $middleware = []): self
    {
        return $this->addRoute('POST', $uri, $handler, $middleware);
    }

    private function addRoute(string $method, string $uri, callable|array $handler, array $middleware): self
    {
        $uri = '/' . trim($uri, '/');
        if ($uri !== '/') {
            $uri = rtrim($uri, '/');
        }
        $this->routes[] = [
            'method' => $method,
            'uri' => $uri,
            'pattern' => $this->compilePattern($uri),
            'handler' => $handler,
            'middleware' => $middleware,
        ];
        return $this;
    }

    private function compilePattern(string $uri): string
    {
        $pattern = preg_quote($uri, '#');
        foreach ($this->patterns as $placeholder => $regex) {
            $pattern = str_replace(preg_quote($placeholder, '#'), $regex, $pattern);
        }
        return '#^' . $pattern . '$#';
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = '/';
        if (isset($_GET['uri'])) {
            $uri = '/' . trim($_GET['uri'], '/');
        }
        if ($uri !== '/') {
            $uri = rtrim($uri, '/');
        }

        // CSRF protection for non-GET requests
        if ($method !== 'GET') {
            if (!verify_csrf()) {
                http_response_code(419);
                die('Session expirée. Veuillez rafraîchir la page et réessayer.');
            }
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['pattern'], $uri, $matches)) {
                array_shift($matches);
                $params = array_values($matches);

                // Run middleware
                foreach ($route['middleware'] as $mw) {
                    if ($mw === 'auth') {
                        \App\Middleware\AuthMiddleware::handle();
                    }
                }

                $handler = $route['handler'];

                if (is_callable($handler)) {
                    echo $handler(...$params);
                } elseif (is_array($handler) && count($handler) === 2) {
                    [$class, $method] = $handler;
                    $controller = new $class();
                    echo $controller->$method(...$params);
                }
                return;
            }
        }

        http_response_code(404);
        View::render('errors/404', ['title' => 'Page non trouvée']);
    }
}
