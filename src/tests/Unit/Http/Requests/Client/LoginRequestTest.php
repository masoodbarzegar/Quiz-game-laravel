<?php

namespace Tests\Unit\Http\Requests\Client;

use App\Http\Requests\Client\LoginRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;


class LoginRequestTest extends TestCase
{
    use RefreshDatabase;

    private LoginRequest $rules;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rules = new LoginRequest();
    }

    #[Test]
    public function it_has_the_correct_rules()
    {
        $this->assertEquals([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ], $this->rules->rules());
    }

    #[Test]
    public function it_is_authorized()
    {
        // Client login requests are typically always authorized (authorization happens in controller/middleware)
        $this->assertTrue($this->rules->authorize());
    }

    #[Test]
    #[DataProvider('validationProvider')]
    public function it_validates_inputs_correctly($data, $passes, $errors = [])
    {
        $validator = Validator::make($data, $this->rules->rules());
        $this->assertEquals($passes, $validator->passes());
        if (!$passes) {
            $this->assertEquals($errors, $validator->errors()->messages());
        }
    }


    public static function validationProvider(): array
    {
        return [
            'request should fail when no email is provided' => [
                'data' => ['password' => 'password'],
                'passes' => false,
                'errors' => ['email' => ['The email field is required.']],
            ],
            'request should fail when email is invalid' => [
                'data' => ['email' => 'not-an-email', 'password' => 'password'],
                'passes' => false,
                'errors' => ['email' => ['The email field must be a valid email address.']],
            ],
            'request should fail when no password is provided' => [
                'data' => ['email' => 'test@example.com'],
                'passes' => false,
                'errors' => ['password' => ['The password field is required.']],
            ],
            'request should pass with valid data' => [
                'data' => ['email' => 'test@example.com', 'password' => 'password', 'remember' => true],
                'passes' => true,
            ],
            'request should pass with remember false' => [
                'data' => ['email' => 'test@example.com', 'password' => 'password', 'remember' => false],
                'passes' => true,
            ],
            'request should pass with remember omitted' => [
                'data' => ['email' => 'test@example.com', 'password' => 'password'],
                'passes' => true,
            ],
        ];
    }
} 