<?php

namespace Tests\Unit\Http\Requests\Client;

use App\Http\Requests\Client\RegisterRequest;
use App\Models\Client as ClientModel; // Alias to avoid conflict if any
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use Illuminate\Validation\Rules\Password;


class RegisterRequestTest extends TestCase
{
    use RefreshDatabase;

    private RegisterRequest $rules;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rules = new RegisterRequest();
    }

    #[Test]
    public function it_has_the_correct_rules()
    {
        $expectedRules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:clients'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'phone' => ['nullable', 'string', 'max:20']
        ];
        $this->assertEquals($expectedRules, $this->rules->rules());
    }

    #[Test]
    public function it_is_authorized()
    {
        // Client registration requests are typically always authorized
        $this->assertTrue($this->rules->authorize());
    }

    #[Test]
    #[DataProvider('validationProvider')]
    public function it_validates_inputs_correctly($data, $passes, $errors = [])
    {
        // If data provider returns a closure, resolve it to get the actual data
        if ($data instanceof \Closure) {
            $data = $data();
        }

        $validator = Validator::make($data, $this->rules->rules());
        $this->assertEquals($passes, $validator->passes(), json_encode($validator->errors()));
        if (!$passes) {
            $this->assertEquals($errors, $validator->errors()->messages());
        }
    }

    public static function validationProvider(): array
    {
        return [
            'request_should_fail_when_name_is_missing' => [
                'data' => [/* name missing */ 'email' => 'client@example.com', 'password' => 'password123', 'password_confirmation' => 'password123'],
                'passes' => false,
                'errors' => ['name' => ['The name field is required.']]
            ],
            'request_should_fail_when_email_is_not_unique' => [
                'data' => function () {
                    ClientModel::factory()->create(['email' => 'existingclient@example.com']);
                    return ['name' => 'Test Client', 'email' => 'existingclient@example.com', 'password' => 'password123', 'password_confirmation' => 'password123'];
                },
                'passes' => false,
                'errors' => ['email' => ['The email has already been taken.']]
            ],
            'request_should_fail_when_password_is_too_short' => [
                'data' => ['name' => 'Test Client', 'email' => 'newclient@example.com', 'password' => 'short', 'password_confirmation' => 'short'],
                'passes' => false,
                'errors' => ['password' => ['The password field must be at least 8 characters.']]
            ],
            'request_should_fail_when_password_confirmation_does_not_match' => [
                'data' => ['name' => 'Test Client', 'email' => 'newclient2@example.com', 'password' => 'password123', 'password_confirmation' => 'different'],
                'passes' => false,
                'errors' => ['password' => ['The password field confirmation does not match.']]
            ],
            'request_should_pass_with_valid_data' => [
                'data' => ['name' => 'Test Client', 'email' => 'validclient@example.com', 'password' => 'password123', 'password_confirmation' => 'password123'],
                'passes' => true,
            ],
        ];
    }
} 