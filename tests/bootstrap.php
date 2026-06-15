<?php

declare(strict_types=1);

/**
 * PHPUnit Bootstrap for Epsilon
 */
require_once __DIR__ . '/../app/Helpers/functions.php';
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Core/Validator.php';
require_once __DIR__ . '/../app/Core/Session.php';
require_once __DIR__ . '/../app/Core/Uploader.php';
require_once __DIR__ . '/../app/Models/User.php';
require_once __DIR__ . '/../app/Models/Course.php';
require_once __DIR__ . '/../app/Models/Challenge.php';
require_once __DIR__ . '/../app/Models/Submission.php';
require_once __DIR__ . '/../app/Models/Evaluation.php';
require_once __DIR__ . '/../app/Models/Badge.php';

// Use in-memory SQLite database for tests
putenv('DB_DRIVER=sqlite');
putenv('DB_PATH=tests/testing.sqlite');
