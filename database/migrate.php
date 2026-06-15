<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/Helpers/functions.php';
require_once __DIR__ . '/../app/Core/Database.php';

use App\Core\Database;

try {
    Database::migrate(__DIR__ . '/schema.sql');
    echo "Migration terminée avec succès.\n";
} catch (\Exception $e) {
    echo "Erreur de migration: " . $e->getMessage() . "\n";
    exit(1);
}
