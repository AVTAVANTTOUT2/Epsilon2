<?php

declare(strict_types=1);

/**
 * Epsilon Test Runner
 *
 * Lance les tests unitaires et d'intégration.
 * Usage: php tests/run.php
 */

$rootDir = dirname(__DIR__);
$_ENV['APP_ENV'] = 'testing';

// Forcer SQLite in-memory pour les tests
$_ENV['DB_DRIVER'] = 'sqlite';
$_ENV['DB_PATH'] = ':memory:';
putenv('DB_DRIVER=sqlite');
putenv('DB_PATH=:memory:');

require_once $rootDir . '/app/Helpers/functions.php';
require_once $rootDir . '/app/Core/Database.php';
require_once $rootDir . '/app/Core/Session.php';
require_once $rootDir . '/app/Core/Validator.php';
require_once $rootDir . '/app/Models/User.php';
require_once $rootDir . '/app/Models/Course.php';
require_once $rootDir . '/app/Models/Challenge.php';
require_once $rootDir . '/app/Models/Submission.php';
require_once $rootDir . '/app/Models/Evaluation.php';
require_once $rootDir . '/app/Models/Badge.php';

use App\Core\Validator;
use App\Models\Course;
use App\Core\Database;

$passed = 0;
$failed = 0;
$errors = [];

function test(string $name, callable $fn): void
{
    global $passed, $failed, $errors;
    try {
        $fn();
        $passed++;
        echo "  \033[32m✓\033[0m {$name}\n";
    } catch (\Throwable $e) {
        $failed++;
        $errors[] = "{$name}: {$e->getMessage()}";
        echo "  \033[31m✗\033[0m {$name}\n";
        echo "    \033[31m{$e->getMessage()}\033[0m\n";
    }
}

function assert_true(mixed $value, string $message = ''): void
{
    if ($value !== true) {
        $msg = $message ?: 'Expected true, got ' . var_export($value, true);
        throw new \AssertionError($msg);
    }
}

function assert_false(mixed $value, string $message = ''): void
{
    if ($value !== false) {
        $msg = $message ?: 'Expected false, got ' . var_export($value, true);
        throw new \AssertionError($msg);
    }
}

function assert_equals(mixed $expected, mixed $actual, string $message = ''): void
{
    if ($expected !== $actual) {
        $msg = $message ?: sprintf('Expected %s, got %s', var_export($expected, true), var_export($actual, true));
        throw new \AssertionError($msg);
    }
}

function assert_contains(string $needle, string $haystack, string $message = ''): void
{
    if (!str_contains($haystack, $needle)) {
        $msg = $message ?: sprintf("Expected '%s' to contain '%s'", $haystack, $needle);
        throw new \AssertionError($msg);
    }
}

echo "\n\033[1;36m══════════════════════════════════════════\033[0m\n";
echo "\033[1;36m  EPSILON TEST SUITE\033[0m\n";
echo "\033[1;36m══════════════════════════════════════════\033[0m\n\n";

// ── Validator Tests ──
echo "\033[1;33m[Validator Tests]\033[0m\n";
test('required field passes when value present', function () {
    $v = new Validator(['name' => 'Jean']);
    $v->required('name');
    assert_true($v->passes());
});

test('required field fails when empty', function () {
    $v = new Validator(['name' => '']);
    $v->required('name');
    assert_false($v->passes());
});

test('required field fails when missing', function () {
    $v = new Validator([]);
    $v->required('name');
    assert_false($v->passes());
});

test('email validation passes for valid email', function () {
    $v = new Validator(['email' => 'test@example.com']);
    $v->email('email');
    assert_true($v->passes());
});

test('email validation fails for invalid email', function () {
    $v = new Validator(['email' => 'not-an-email']);
    $v->email('email');
    assert_false($v->passes());
});

test('min length passes', function () {
    $v = new Validator(['password' => '12345678']);
    $v->minLength('password', 8);
    assert_true($v->passes());
});

test('min length fails', function () {
    $v = new Validator(['password' => '123']);
    $v->minLength('password', 8);
    assert_false($v->passes());
});

test('matches passes when identical', function () {
    $v = new Validator(['password' => 'secret', 'password_confirmation' => 'secret']);
    $v->matches('password', 'password_confirmation', 'confirmation');
    assert_true($v->passes());
});

test('matches fails when different', function () {
    $v = new Validator(['password' => 'secret', 'password_confirmation' => 'diff']);
    $v->matches('password', 'password_confirmation', 'confirmation');
    assert_false($v->passes());
});

test('range validation', function () {
    $v = new Validator(['score' => '3']);
    $v->range('score', 1, 5);
    assert_true($v->passes());
    $v2 = new Validator(['score' => '0']);
    $v2->range('score', 1, 5);
    assert_false($v2->passes());
});

test('multiple rules accumulate errors', function () {
    $v = new Validator(['email' => '', 'password' => 'short']);
    $v->required('email', 'password')->email('email')->minLength('password', 8);
    assert_false($v->passes());
});

// ── Course Access Tests ──
echo "\n\033[1;33m[Course Access Tests]\033[0m\n";
test('rank name returns correct labels', function () {
    assert_equals('Non suivi', Course::rankName(0));
    assert_equals('Apprenti', Course::rankName(2));
    assert_equals('Guide', Course::rankName(5));
});

test('can join course 0 when not joined', function () {
    assert_true(Course::canJoin(0, [0, 0, 0, 0]));
});

test('cannot join course 0 when already joined', function () {
    assert_false(Course::canJoin(0, [1, 0, 0, 0]));
});

test('can join course 1 at Apprenti level', function () {
    assert_true(Course::canJoin(1, [0, 2, 0, 0]));
});

test('cannot join course 1 below Apprenti', function () {
    assert_false(Course::canJoin(1, [0, 1, 0, 0]));
    assert_false(Course::canJoin(1, [0, 0, 0, 0]));
});

test('can join course 2 when course 0 at Apprenti', function () {
    assert_true(Course::canJoin(2, [2, 0, 0, 0]));
});

test('cannot join course 2 without course 0 Apprenti', function () {
    assert_false(Course::canJoin(2, [1, 0, 0, 0]));
});

test('isJoined returns correct values', function () {
    assert_true(Course::isJoined(0, [1, 0, 0, 0]));
    assert_false(Course::isJoined(0, [0, 0, 0, 0]));
});

test('getNextChallengeLevel', function () {
    assert_equals(1, Course::getNextChallengeLevel(0, [0, 0, 0, 0]));
    assert_equals(3, Course::getNextChallengeLevel(0, [2, 0, 0, 0]));
});

// ── Database Integration Tests ──
echo "\n\033[1;33m[Database Integration Tests]\033[0m\n";

// Setup test database (in-memory, fresh each run)
Database::disconnect();
Database::reset();

Database::connect()->exec('
    CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        email VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        name VARCHAR(100) NOT NULL DEFAULT "",
        access_code VARCHAR(50) NOT NULL DEFAULT "0 0 0 0",
        email_verified_at DATETIME DEFAULT NULL,
        email_verify_token VARCHAR(64) DEFAULT NULL,
        password_reset_token VARCHAR(64) DEFAULT NULL,
        password_reset_expires_at DATETIME DEFAULT NULL,
        remember_token VARCHAR(64) DEFAULT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    );
    CREATE TABLE IF NOT EXISTS badges (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name VARCHAR(100) NOT NULL,
        description TEXT NOT NULL DEFAULT "",
        icon VARCHAR(50) NOT NULL DEFAULT "fa-circle",
        color VARCHAR(7) NOT NULL DEFAULT "#6366f1",
        badge_level INTEGER NOT NULL DEFAULT 1,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    );
    CREATE TABLE IF NOT EXISTS user_badges (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        badge_id INTEGER NOT NULL,
        earned_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (badge_id) REFERENCES badges(id) ON DELETE CASCADE,
        UNIQUE(user_id, badge_id)
    );
    INSERT OR IGNORE INTO badges (id, name, icon, badge_level) VALUES
    (1, "Apprenti", "fa-graduation-cap", 2),
    (2, "Compagnon", "fa-handshake", 3);
');

test('create and find user', function () {
    $id = \App\Models\User::create([
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);
    $user = \App\Models\User::findById($id);
    assert_true($user !== null);
    assert_equals('test@example.com', $user['email']);
});

test('password is hashed with bcrypt', function () {
    $id = \App\Models\User::create([
        'email' => 'hash@example.com',
        'password' => 'mypassword',
    ]);
    $user = \App\Models\User::findById($id);
    assert_true(password_verify('mypassword', $user['password']));
});

test('default access code is 0 0 0 0', function () {
    $id = \App\Models\User::create([
        'email' => 'access@example.com',
        'password' => 'test1234',
    ]);
    assert_equals('0 0 0 0', \App\Models\User::getAccessCode($id));
});

test('update access code at index', function () {
    $id = \App\Models\User::create([
        'email' => 'update@example.com',
        'password' => 'test1234',
    ]);
    \App\Models\User::updateAccessCodeAtIndex($id, 0, 2);
    assert_equals('2 0 0 0', \App\Models\User::getAccessCode($id));
});

test('auto award badges', function () {
    $id = \App\Models\User::create([
        'email' => 'badges@example.com',
        'password' => 'test1234',
    ]);
    \App\Models\User::updateAccessCodeAtIndex($id, 0, 2);
    \App\Models\Badge::autoAward($id);
    $badges = \App\Models\Badge::findByUser($id);
    assert_true(count($badges) > 0);
});

test('email verify with valid token', function () {
    $id = \App\Models\User::create([
        'email' => 'verify@example.com',
        'password' => 'test1234',
        'email_verify_token' => bin2hex(random_bytes(16)),
    ]);

    $user = \App\Models\User::findById($id);
    assert_equals(null, $user['email_verified_at']);

    \App\Models\User::verifyEmail($id);
    $user = \App\Models\User::findById($id);
    assert_true($user['email_verified_at'] !== null);
    assert_equals(null, $user['email_verify_token']);
});

test('find by email', function () {
    \App\Models\User::create([
        'email' => 'findme@example.com',
        'password' => 'test1234',
    ]);
    $user = \App\Models\User::findByEmail('findme@example.com');
    assert_true($user !== null);

    $notFound = \App\Models\User::findByEmail('nope@example.com');
    assert_equals(null, $notFound);
});

// ── Helpers Tests ──
echo "\n\033[1;33m[Helpers Tests]\033[0m\n";

test('e() escapes HTML', function () {
    assert_equals('&lt;script&gt;', e('<script>'));
    assert_equals("&#039;test&#039;", e("'test'"));
});

test('slugify creates valid slugs', function () {
    assert_equals('hello-world', slugify('Hello World'));
    assert_equals('test', slugify('  Test  '));
});

// ── Ranking Methods Tests ──
echo "\n\033[1;33m[Ranking Tests]\033[0m\n";

test('rank icon returns correct icons', function () {
    assert_equals('fa-circle', Course::rankIcon(0));
    assert_equals('fa-graduation-cap', Course::rankIcon(2));
    assert_equals('fa-star', Course::rankIcon(5));
});

test('rank class returns correct classes', function () {
    assert_equals('rank-none', Course::rankClass(0));
    assert_equals('rank-following', Course::rankClass(1));
    assert_equals('rank-apprentice', Course::rankClass(2));
    assert_equals('rank-companion', Course::rankClass(3));
    assert_equals('rank-passer', Course::rankClass(4));
    assert_equals('rank-guide', Course::rankClass(5));
});

// Cleanup
Database::disconnect();
Database::reset();

// ── Results ──
echo "\n\033[1;36m══════════════════════════════════════════\033[0m\n";
$total = $passed + $failed;
echo sprintf("\n  Tests: %d | \033[32mPassed: %d\033[0m | \033[31mFailed: %d\033[0m\n", $total, $passed, $failed);

if ($failed === 0) {
    echo "  \033[32mAll tests passed!\033[0m\n\n";
} else {
    echo "\n  \033[31mFailures:\033[0m\n";
    foreach ($errors as $error) {
        echo "  - {$error}\n";
    }
    echo "\n";
}

exit($failed > 0 ? 1 : 0);
