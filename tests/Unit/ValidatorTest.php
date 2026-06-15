<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Core\Validator;

final class ValidatorTest extends TestCase
{
    public function test_required_field_passes_when_value_present(): void
    {
        $validator = new Validator(['name' => 'Jean']);
        $validator->required('name');
        $this->assertTrue($validator->passes());
    }

    public function test_required_field_fails_when_value_missing(): void
    {
        $validator = new Validator(['name' => '']);
        $validator->required('name');
        $this->assertFalse($validator->passes());
    }

    public function test_required_field_fails_when_key_absent(): void
    {
        $validator = new Validator([]);
        $validator->required('name');
        $this->assertFalse($validator->passes());
        $this->assertNotNull($validator->error('name'));
    }

    public function test_email_validation_passes_for_valid_email(): void
    {
        $validator = new Validator(['email' => 'test@example.com']);
        $validator->email('email');
        $this->assertTrue($validator->passes());
    }

    public function test_email_validation_fails_for_invalid_email(): void
    {
        $validator = new Validator(['email' => 'not-an-email']);
        $validator->email('email');
        $this->assertFalse($validator->passes());
    }

    public function test_min_length_passes(): void
    {
        $validator = new Validator(['password' => '12345678']);
        $validator->minLength('password', 8);
        $this->assertTrue($validator->passes());
    }

    public function test_min_length_fails(): void
    {
        $validator = new Validator(['password' => '123']);
        $validator->minLength('password', 8);
        $this->assertFalse($validator->passes());
    }

    public function test_max_length_passes(): void
    {
        $validator = new Validator(['bio' => 'Short']);
        $validator->maxLength('bio', 100);
        $this->assertTrue($validator->passes());
    }

    public function test_max_length_fails(): void
    {
        $validator = new Validator(['bio' => str_repeat('a', 101)]);
        $validator->maxLength('bio', 100);
        $this->assertFalse($validator->passes());
    }

    public function test_matches_passes_when_fields_identical(): void
    {
        $validator = new Validator(['password' => 'secret', 'password_confirmation' => 'secret']);
        $validator->matches('password', 'password_confirmation', 'confirmation');
        $this->assertTrue($validator->passes());
    }

    public function test_matches_fails_when_fields_differ(): void
    {
        $validator = new Validator(['password' => 'secret', 'password_confirmation' => 'different']);
        $validator->matches('password', 'password_confirmation', 'confirmation');
        $this->assertFalse($validator->passes());
    }

    public function test_integer_passes(): void
    {
        $validator = new Validator(['age' => '25']);
        $validator->integer('age');
        $this->assertTrue($validator->passes());
    }

    public function test_integer_fails(): void
    {
        $validator = new Validator(['age' => 'abc']);
        $validator->integer('age');
        $this->assertFalse($validator->passes());
    }

    public function test_range_passes(): void
    {
        $validator = new Validator(['score' => '3']);
        $validator->range('score', 1, 5);
        $this->assertTrue($validator->passes());
    }

    public function test_range_fails(): void
    {
        $validator = new Validator(['score' => '0']);
        $validator->range('score', 1, 5);
        $this->assertFalse($validator->passes());
    }

    public function test_in_array_passes(): void
    {
        $validator = new Validator(['status' => 'pending']);
        $validator->inArray('status', ['pending', 'reviewed']);
        $this->assertTrue($validator->passes());
    }

    public function test_in_array_fails(): void
    {
        $validator = new Validator(['status' => 'invalid']);
        $validator->inArray('status', ['pending', 'reviewed']);
        $this->assertFalse($validator->passes());
    }

    public function test_multiple_rules_accumulate_errors(): void
    {
        $validator = new Validator(['email' => '', 'password' => 'short']);
        $validator->required('email', 'password')->email('email')->minLength('password', 8);
        $this->assertFalse($validator->passes());
        $this->assertNotEmpty($validator->errors());
    }

    public function test_custom_rule(): void
    {
        $validator = new Validator(['value' => 'foo']);
        $validator->custom('value', fn($v) => $v === 'foo', 'Expected foo');
        $this->assertTrue($validator->passes());
    }

    public function test_custom_rule_fails(): void
    {
        $validator = new Validator(['value' => 'bar']);
        $validator->custom('value', fn($v) => $v === 'foo', 'Expected foo');
        $this->assertFalse($validator->passes());
    }
}
