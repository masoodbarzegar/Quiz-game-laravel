<?php

namespace Tests\Unit\Http\Requests\Admin;

use App\Http\Requests\Admin\LoginRequest;
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
    public function it_has_the_correct_rules(): void
    {
        $this->assertEquals([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ], $this->rules->rules());
    }

    #[Test]
    public function it_is_authorized(): void
    {
        $this->assertTrue($this->rules->authorize());
    }

    #[Test]
    #[DataProvider('validationDataset')]
    public function validation_passes_or_fails_as_expected(array $data, bool $passes, array $errors = []): void
    {
        $validator = Validator::make($data, $this->rules->rules());
        $this->assertEquals($passes, $validator->passes(), json_encode($validator->errors()->getMessages()));
        if (!$passes) {
            $this->assertEquals($errors, $validator->errors()->messages());
        }
    }
    
    public static function validationDataset(): array
    {
        return [
            'request_should_fail_when_no_email_is_provided' => [
                'data' => ['password' => 'password'],
                'passes' => false,
                'errors' => ['email' => ['The email field is required.']]
            ],
            'request_should_fail_when_email_is_invalid' => [
                'data' => ['email' => 'not-an-email', 'password' => 'password'],
                'passes' => false,
                'errors' => ['email' => ['The email field must be a valid email address.']]
            ],
            'request_should_fail_when_no_password_is_provided' => [
                'data' => ['email' => 'test@example.com'],
                'passes' => false,
                'errors' => ['password' => ['The password field is required.']]
            ],
            'request_should_pass_with_valid_data' => [
                'data' => ['email' => 'test@example.com', 'password' => 'password', 'remember' => true],
                'passes' => true,
            ],
            'request_should_pass_with_valid_data_and_remember_false' => [
                'data' => ['email' => 'test@example.com', 'password' => 'password', 'remember' => false],
                'passes' => true,
            ],
            'request_should_pass_with_valid_data_and_remember_not_provided' => [
                'data' => ['email' => 'test@example.com', 'password' => 'password'],
                'passes' => true,
            ],
        ];
    }
}