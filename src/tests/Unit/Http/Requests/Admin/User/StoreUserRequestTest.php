<?php

namespace Tests\Unit\Http\Requests\Admin\User;

use App\Http\Requests\Admin\User\StoreUserRequest;
use App\Models\User as UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use Illuminate\Validation\Rules\Password;

class StoreUserRequestTest extends TestCase
{
    use RefreshDatabase;

    private StoreUserRequest $request;

    protected function setUp(): void
    {
        parent::setUp();
        $this->request = new StoreUserRequest();
    }

    #[Test]
    public function it_is_authorized_for_manager_role(): void
    {
        $managerUser = UserModel::factory()->create(['role' => 'manager']);
        $this->request->setUserResolver(fn() => $managerUser);
        $this->assertTrue($this->request->authorize(), 'Manager should be authorized.');

        $nonManagerUser = UserModel::factory()->create(['role' => 'corrector']);
        $this->request->setUserResolver(fn() => $nonManagerUser);
        $this->assertFalse($this->request->authorize(), 'Corrector should not be authorized.');
    }

    #[Test]
    public function it_has_the_correct_rules(): void
    {
        $expectedRules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required', 'string', 'in:manager,corrector,general'],
        ];
        
        $actualRules = $this->request->rules();
        $this->assertEquals(array_keys($expectedRules), array_keys($actualRules)); // Check keys first

        // Check specific rules, handling Password::defaults() by class type
        $this->assertEquals($expectedRules['name'], $actualRules['name']);
        $this->assertEquals($expectedRules['email'], $actualRules['email']);
        $this->assertEquals([$expectedRules['password'][0], $expectedRules['password'][1]], [$actualRules['password'][0], $actualRules['password'][1]]);
        $this->assertInstanceOf(Password::class, $actualRules['password'][2]);
        $this->assertEquals($expectedRules['role'], $actualRules['role']);
    }

    #[Test]
    #[DataProvider('validationDataset')]
    public function validation_passes_or_fails_as_expected(array|callable $data, bool $passes, array $expectedErrors = []): void
    {
        if (is_callable($data)) {
            $data = $data();
        }
        
        $validator = Validator::make($data, $this->request->rules(), $this->request->messages());
        $this->assertEquals($passes, $validator->passes(), json_encode($validator->errors()->all()));
        if (!$passes) {
            $this->assertEquals($expectedErrors, $validator->errors()->messages());
        }
    }
    
    public static function validationDataset(): array
    {
        return [
            'name_is_missing' => [
                'data' => [/* name missing */ 'email' => 'test@example.com', 'password' => 'ValidPassword123', 'password_confirmation' => 'ValidPassword123', 'role' => 'general'],
                'passes' => false,
                'expectedErrors' => ['name' => ['The name field is required.']]
            ],
            'email_is_not_unique' => [
                'data' => function () {
                    UserModel::factory()->create(['email' => 'existing@example.com']);
                    return ['name' => 'Test User', 'email' => 'existing@example.com', 'password' => 'ValidPassword123', 'password_confirmation' => 'ValidPassword123', 'role' => 'general'];
                },
                'passes' => false,
                'expectedErrors' => ['email' => ['The email has already been taken.']]
            ],
            'password_is_too_short' => [
                'data' => ['name' => 'Test User', 'email' => 'test@example.com', 'password' => 'short', 'password_confirmation' => 'short', 'role' => 'general'],
                'passes' => false,
                'expectedErrors' => ['password' => ['The password field must be at least 8 characters.']]
            ],
            'password_confirmation_does_not_match' => [
                'data' => ['name' => 'Test User', 'email' => 'existing@example.com', 'password' => 'ValidPassword123', 'password_confirmation' => 'different', 'role' => 'general'],
                'passes' => false,
                'expectedErrors' => ['password' => ['The password field confirmation does not match.']]
            ],
            'role_is_invalid' => [
                'data' => ['name' => 'Test User', 'email' => 'existing@example.com', 'password' => 'ValidPassword123', 'password_confirmation' => 'ValidPassword123', 'role' => 'invalid_role'],
                'passes' => false,
                'expectedErrors' => ['role' => ['The selected role is invalid. Must be one of: Manager, Corrector, General Admin.']]
            ],
            'passes_with_valid_data_manager' => [
                'data' => ['name' => 'Test Manager', 'email' => 'existing@example.com', 'password' => 'ValidPassword123', 'password_confirmation' => 'ValidPassword123', 'role' => 'manager'],
                'passes' => true,
            ],
            'passes_with_valid_data_corrector' => [
                'data' => ['name' => 'Test Corrector', 'email' => 'existing@example.com', 'password' => 'ValidPassword123', 'password_confirmation' => 'ValidPassword123', 'role' => 'corrector'],
                'passes' => true,
            ],
            'passes_with_valid_data_general' => [
                'data' => ['name' => 'Test General', 'email' => 'existing@example.com', 'password' => 'ValidPassword123', 'password_confirmation' => 'ValidPassword123', 'role' => 'general'],
                'passes' => true,
            ],
        ];
    }
} 