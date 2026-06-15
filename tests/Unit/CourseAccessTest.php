<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\Course;

final class CourseAccessTest extends TestCase
{
    public function test_rank_name_returns_correct_labels(): void
    {
        $this->assertSame('Non suivi', Course::rankName(0));
        $this->assertSame('Suivi', Course::rankName(1));
        $this->assertSame('Apprenti', Course::rankName(2));
        $this->assertSame('Compagnon', Course::rankName(3));
        $this->assertSame('Passeur', Course::rankName(4));
        $this->assertSame('Guide', Course::rankName(5));
        $this->assertSame('Inconnu', Course::rankName(99));
    }

    public function test_rank_icon_returns_correct_icons(): void
    {
        $this->assertSame('fa-circle', Course::rankIcon(0));
        $this->assertSame('fa-graduation-cap', Course::rankIcon(2));
        $this->assertSame('fa-star', Course::rankIcon(5));
    }

    /** Course 0: accessible when not joined (code[0] == 0) */
    public function test_can_join_course_0_when_not_joined(): void
    {
        $this->assertTrue(Course::canJoin(0, [0, 0, 0, 0]));
    }

    /** Course 0: not joinable when already joined (code[0] >= 1) */
    public function test_cannot_join_course_0_when_already_joined(): void
    {
        $this->assertFalse(Course::canJoin(0, [1, 0, 0, 0]));
    }

    /** Course 1: accessible when code[1] >= 2 (Apprenti) */
    public function test_can_join_course_1_when_level_apprenti_or_higher(): void
    {
        $this->assertTrue(Course::canJoin(1, [0, 2, 0, 0]));
    }

    /** Course 1: not accessible below Apprenti level */
    public function test_cannot_join_course_1_when_below_apprenti(): void
    {
        $this->assertFalse(Course::canJoin(1, [0, 1, 0, 0]));
        $this->assertFalse(Course::canJoin(1, [0, 0, 0, 0]));
    }

    /** Course 2: accessible when code[0] >= 2 */
    public function test_can_join_course_2_when_course_0_is_apprenti(): void
    {
        $this->assertTrue(Course::canJoin(2, [2, 0, 0, 0]));
        $this->assertTrue(Course::canJoin(2, [3, 0, 0, 0]));
    }

    /** Course 2: not accessible otherwise */
    public function test_cannot_join_course_2_without_course_0_apprenti(): void
    {
        $this->assertFalse(Course::canJoin(2, [1, 0, 0, 0]));
        $this->assertFalse(Course::canJoin(2, [0, 0, 0, 0]));
    }

    /** Course 3: accessible when code[0] >= 2 */
    public function test_can_join_course_3_when_course_0_is_apprenti(): void
    {
        $this->assertTrue(Course::canJoin(3, [2, 0, 0, 0]));
    }

    /** Course 3: not accessible otherwise */
    public function test_cannot_join_course_3_without_course_0_apprenti(): void
    {
        $this->assertFalse(Course::canJoin(3, [1, 0, 0, 0]));
    }

    public function test_is_joined_returns_true_when_level_1_or_higher(): void
    {
        $this->assertTrue(Course::isJoined(0, [1, 0, 0, 0]));
        $this->assertTrue(Course::isJoined(0, [2, 0, 0, 0]));
    }

    public function test_is_joined_returns_false_when_level_0(): void
    {
        $this->assertFalse(Course::isJoined(0, [0, 0, 0, 0]));
    }

    public function test_get_next_challenge_level(): void
    {
        $this->assertSame(1, Course::getNextChallengeLevel(0, [0, 0, 0, 0]));
        $this->assertSame(2, Course::getNextChallengeLevel(0, [1, 0, 0, 0]));
        $this->assertSame(3, Course::getNextChallengeLevel(0, [2, 0, 0, 0]));
    }
}
