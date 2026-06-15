<?php

declare(strict_types=1);

namespace App\Core;

final class View
{
    private static string $layout = 'layout';

    public static function render(string $view, array $data = [], bool $useLayout = true): string
    {
        extract($data);
        $viewPath = dirname(__DIR__, 2) . '/views/' . $view . '.php';

        if (!file_exists($viewPath)) {
            throw new \RuntimeException("Vue introuvable: {$view}");
        }

        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        if ($useLayout) {
            $layoutPath = dirname(__DIR__, 2) . '/views/' . self::$layout . '.php';
            if (!file_exists($layoutPath)) {
                throw new \RuntimeException("Layout introuvable: " . self::$layout);
            }
            ob_start();
            require $layoutPath;
            return ob_get_clean();
        }

        return $content;
    }

    public static function setLayout(string $layout): void
    {
        self::$layout = $layout;
    }

    public static function partial(string $partial, array $data = []): string
    {
        extract($data);
        $path = dirname(__DIR__, 2) . '/views/partials/' . $partial . '.php';
        if (!file_exists($path)) {
            return "<!-- partial {$partial} introuvable -->";
        }
        ob_start();
        require $path;
        return ob_get_clean();
    }
}
