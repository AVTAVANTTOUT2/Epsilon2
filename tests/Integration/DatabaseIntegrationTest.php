<?php

declare(strict_types=1);

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use App\Core\Database;
use App\Models\User;
use App\Models\Course;
use App\Models\Badge;
use App\Models\Submission as SubmissionModel;
use App\Models\Evaluation as EvaluationModel;

final class DatabaseIntegrationTest extends TestCase
{
    private string $testDb;

    protected function setUp(): void
    {
        Database::reset();
        $this->testDb = dirname(__DIR__) . '/testing.sqlite';

        // Clean up
        if (file_exists($this->testDb)) {
            unlink($this->testDb);
        }
        if (file_exists($this->testDb . '-wal')) {
            unlink($this->testDb . '-wal');
        }
        if (file_exists($this->testDb . '-shm')) {
            unlink($this->testDb . '-shm');
        }

        // Run schema
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
            CREATE TABLE IF NOT EXISTS courses (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                course_index INTEGER NOT NULL DEFAULT 0,
                name VARCHAR(255) NOT NULL,
                description TEXT NOT NULL DEFAULT "",
                icon VARCHAR(50) NOT NULL DEFAULT "fa-book",
                color VARCHAR(7) NOT NULL DEFAULT "#6366f1",
                is_active INTEGER NOT NULL DEFAULT 1,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            );
            CREATE TABLE IF NOT EXISTS challenges (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                course_id INTEGER NOT NULL,
                rank_level INTEGER NOT NULL,
                title VARCHAR(255) NOT NULL,
                description TEXT NOT NULL DEFAULT "",
                challenge_order INTEGER NOT NULL DEFAULT 0,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
            );
            CREATE TABLE IF NOT EXISTS submissions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                challenge_id INTEGER NOT NULL,
                file_path VARCHAR(500) NOT NULL,
                original_filename VARCHAR(255) NOT NULL,
                file_type VARCHAR(50) NOT NULL DEFAULT "",
                file_size INTEGER NOT NULL DEFAULT 0,
                status VARCHAR(20) NOT NULL DEFAULT "pending",
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (challenge_id) REFERENCES challenges(id) ON DELETE CASCADE
            );
            CREATE TABLE IF NOT EXISTS evaluations (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                submission_id INTEGER NOT NULL,
                evaluator_id INTEGER NOT NULL,
                score INTEGER NOT NULL CHECK(score >= 1 AND score <= 5),
                comment TEXT NOT NULL DEFAULT "",
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (submission_id) REFERENCES submissions(id) ON DELETE CASCADE,
                FOREIGN KEY (evaluator_id) REFERENCES users(id) ON DELETE CASCADE,
                UNIQUE(submission_id, evaluator_id)
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
        ');

        // Seed badges
        Database::connect()->exec("
            INSERT OR IGNORE INTO badges (id, name, description, icon, color, badge_level) VALUES
            (1, 'Apprenti', 'Test', 'fa-graduation-cap', '#6366f1', 2),
            (2, 'Compagnon', 'Test', 'fa-handshake', '#10b981', 3),
            (3, 'Passeur', 'Test', 'fa-hand-holding-heart', '#f59e0b', 4),
            (4, 'Guide', 'Test', 'fa-star', '#ef4444', 5);
        ");
    }

    protected function tearDown(): void
    {
        Database::reset();
        if (file_exists($this->testDb)) {
            unlink($this->testDb);
        }
    }

    public function test_create_and_find_user(): void
    {
        $id = User::create([
            'email' => 'test@example.com',
            'password' => 'password123',
            'name' => 'Test User',
        ]);

        $this->assertGreaterThan(0, $id);

        $user = User::findById($id);
        $this->assertNotNull($user);
        $this->assertSame('test@example.com', $user['email']);
        $this->assertNotEmpty($user['password']);
    }

    public function test_find_user_by_email(): void
    {
        User::create([
            'email' => 'unique@example.com',
            'password' => 'test1234',
        ]);

        $user = User::findByEmail('unique@example.com');
        $this->assertNotNull($user);
        $this->assertSame('unique@example.com', $user['email']);
    }

    public function test_password_is_hashed(): void
    {
        $id = User::create([
            'email' => 'hash@example.com',
            'password' => 'mypassword',
        ]);

        $user = User::findById($id);
        $this->assertNotSame('mypassword', $user['password']);
        $this->assertTrue(password_verify('mypassword', $user['password']));
    }

    public function test_default_access_code(): void
    {
        $id = User::create([
            'email' => 'access@example.com',
            'password' => 'password123',
        ]);

        $code = User::getAccessCode($id);
        $this->assertSame('0 0 0 0', $code);
    }

    public function test_update_access_code(): void
    {
        $id = User::create([
            'email' => 'update@example.com',
            'password' => 'password123',
        ]);

        User::updateAccessCodeAtIndex($id, 0, 2);
        $code = User::getAccessCode($id);
        $this->assertSame('2 0 0 0', $code);

        User::updateAccessCodeAtIndex($id, 1, 3);
        $code = User::getAccessCode($id);
        $this->assertSame('2 3 0 0', $code);
    }

    public function test_auto_award_badges(): void
    {
        $id = User::create([
            'email' => 'badges@example.com',
            'password' => 'password123',
        ]);

        // Simulate apprentice level
        User::updateAccessCodeAtIndex($id, 0, 2);
        Badge::autoAward($id);

        $badges = Badge::findByUser($id);
        $this->assertNotEmpty($badges);
        $badgeNames = array_column($badges, 'name');
        $this->assertContains('Apprenti', $badgeNames);
    }

    public function test_duplicate_email_prevents_insert(): void
    {
        User::create([
            'email' => 'same@example.com',
            'password' => 'first123',
        ]);

        $this->expectException(\Exception::class);
        User::create([
            'email' => 'same@example.com',
            'password' => 'second456',
        ]);
    }
}
