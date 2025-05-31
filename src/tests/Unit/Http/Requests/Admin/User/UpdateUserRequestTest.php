<?php

namespace Tests\Unit\Http\Requests\Admin\User;

use App\Http\Requests\Admin\User\UpdateUserRequest;
use App\Models\User as UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;
use Illuminate\Routing\Route;
use Illuminate\Http\Request as HttpRequest;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequestTest extends TestCase
{
    use RefreshDatabase;

    private UpdateUserRequest $request;
    private UserModel $existingUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->request = new UpdateUserRequest();
        $this->existingUser = UserModel::factory()->create(['email' => 'original@example.com', 'name' => 'Original Name', 'role' => 'general']);

        // Mock the route and inject the existing user, as UpdateUserRequest relies on $this->user or $this->route('user')
        $this->request->setRouteResolver(function () {
            $mockRoute = new Route(['PUT', 'PATCH'], 'admin/users/{user}', function () {});
            // Bind a mock request if needed, though often FormRequest handles this internally
            $mockHttpRequest = HttpRequest::create('/admin/users/' . $this->existingUser->id, 'PUT');
            $mockRoute->bind($mockHttpRequest);
            $mockRoute->setParameter('user', $this->existingUser);
            return $mockRoute;
        });
    }

    #[Test]
    public function it_is_authorized_for_manager_role(): void
    {
        $managerUser = UserModel::factory()->create(['role' => 'manager']);
        $this->actingAs($managerUser, 'admin'); // Set acting user for the application context
        $this->request->setUserResolver(fn() => $managerUser); // Explicitly set user for this request instance
        $this->assertTrue($this->request->authorize(), 'Manager should be authorized.');

        $nonManagerUser = UserModel::factory()->create(['role' => 'corrector']);
        $this->actingAs($nonManagerUser, 'admin');
        $this->request->setUserResolver(fn() => $nonManagerUser);
        $this->assertFalse($this->request->authorize(), 'Corrector should not be authorized for UpdateUserRequest (manager only).');
    }

    #[Test]
    public function it_has_the_correct_rules(): void
    {
        $expectedRules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $this->existingUser->id],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'role' => ['required', 'string', 'in:manager,corrector,general'],
            'is_active' => ['boolean'],
        ];
        
        $actualRules = $this->request->rules();
        $this->assertEquals(array_keys($expectedRules), array_keys($actualRules));

        $this->assertEquals($expectedRules['name'], $actualRules['name']);
        $this->assertEquals($expectedRules['email'], $actualRules['email']);
        $this->assertEquals([$expectedRules['password'][0], $expectedRules['password'][1]], [$actualRules['password'][0], $actualRules['password'][1]]);
        $this->assertInstanceOf(Password::class, $actualRules['password'][2]);
        $this->assertEquals($expectedRules['role'], $actualRules['role']);
        $this->assertEquals($expectedRules['is_active'], $actualRules['is_active']);
    }

    #[Test]
    #[DataProvider('validationDataset')]
    public function validation_passes_or_fails_as_expected(/* callable|array */ $data, bool $passes, array $expectedErrors = []): void
    {
        if (is_callable($data)) {
            $data = $data();
        }
        $validator = Validator::make($data, $this->request->rules(), $this->request->messages());
        $this->assertEquals($passes, $validator->passes(), "Validation failed with errors: " . json_encode($validator->errors()->all()));
        if (!$passes) {
            $this->assertEquals($expectedErrors, $validator->errors()->messages());
        }
    }
   
    public static function validationDataset(): array
    {
        // Base valid data assumes we are updating the existing user defined in setUp()
        // All 'required' fields must be present unless they are the one being tested for absence/invalidity.
        $baseData = fn($existingUser) => [
            'name' => $existingUser->name,
            'email' => $existingUser->email,
            'role' => $existingUser->role,
            'is_active' => $existingUser->is_active ?? true, // Default to true if not set
        ];

        return [
            'passes_with_no_changes_to_data' => [
                'data' => function() use ($baseData) { 
                    // In a real scenario, $this->existingUser would be available if this provider wasn't static.
                    // We simulate by creating a user that matches what existingUser would be.
                    $userForTest = UserModel::factory()->make(['email' => 'original@example.com', 'name' => 'Original Name', 'role' => 'general']);
                    return $baseData($userForTest); 
                },
                'passes' => true,
            ],
            'passes_updating_name' => [
                'data' => function() use ($baseData) {
                    $userForTest = UserModel::factory()->make(['email' => 'original@example.com', 'name' => 'Original Name', 'role' => 'general']);
                    return array_merge($baseData($userForTest), ['name' => 'New Name']);
                },
                'passes' => true,
            ],
            'passes_updating_email_to_new_unique' => [
                'data' => function() use ($baseData) {
                    $userForTest = UserModel::factory()->make(['email' => 'original@example.com', 'name' => 'Original Name', 'role' => 'general']);
                    return array_merge($baseData($userForTest), ['email' => 'new_unique@example.com']);
                },
                'passes' => true,
            ],
            'passes_email_is_same_as_existing' => [
                'data' => function() use ($baseData) {
                    $userForTest = UserModel::factory()->make(['email' => 'original@example.com', 'name' => 'Original Name', 'role' => 'general']);
                    return $baseData($userForTest); // Email is already original@example.com
                },
                'passes' => true,
            ],
            'fails_email_taken_by_another' => [
                'data' => function() use ($baseData) {
                    UserModel::factory()->create(['email' => 'taken@example.com']); // Another user takes this email
                    $userForTest = UserModel::factory()->make(['email' => 'original@example.com', 'name' => 'Original Name', 'role' => 'general']);
                    return array_merge($baseData($userForTest), ['email' => 'taken@example.com']);
                },
                'passes' => false,
                'expectedErrors' => ['email' => ['The email has already been taken.']]
            ],
            'passes_updating_password' => [
                'data' => function() use ($baseData) {
                    $userForTest = UserModel::factory()->make(['email' => 'original@example.com', 'name' => 'Original Name', 'role' => 'general']);
                    return array_merge($baseData($userForTest), ['password' => 'newSecurePassword123', 'password_confirmation' => 'newSecurePassword123']);
                },
                'passes' => true,
            ],
            'fails_password_too_short' => [
                'data' => function() use ($baseData) {
                    $userForTest = UserModel::factory()->make(['email' => 'original@example.com', 'name' => 'Original Name', 'role' => 'general']);
                    return array_merge($baseData($userForTest), ['password' => 'short', 'password_confirmation' => 'short']);
                },
                'passes' => false,
                'expectedErrors' => ['password' => ['The password field must be at least 8 characters.']] // Default for Password::defaults()
            ],
            'fails_password_confirmation_mismatch' => [
                'data' => function() use ($baseData) {
                    $userForTest = UserModel::factory()->make(['email' => 'original@example.com', 'name' => 'Original Name', 'role' => 'general']);
                    return array_merge($baseData($userForTest), ['password' => 'newSecurePassword123', 'password_confirmation' => 'mismatch']);
                },
                'passes' => false,
                'expectedErrors' => ['password' => ['The password field confirmation does not match.']]
            ],
            'passes_updating_role' => [
                'data' => function() use ($baseData) {
                    $userForTest = UserModel::factory()->make(['email' => 'original@example.com', 'name' => 'Original Name', 'role' => 'general']);
                    return array_merge($baseData($userForTest), ['role' => 'manager']);
                },
                'passes' => true,
            ],
            'fails_role_invalid' => [
                'data' => function() use ($baseData) {
                    $userForTest = UserModel::factory()->make(['email' => 'original@example.com', 'name' => 'Original Name', 'role' => 'general']);
                    return array_merge($baseData($userForTest), ['role' => 'invalid_role']);
                },
                'passes' => false,
                'expectedErrors' => ['role' => ['The selected role is invalid. Must be one of: Manager, Corrector, General Admin.']]
            ],
            'passes_updating_is_active' => [
                'data' => function() use ($baseData) {
                    $userForTest = UserModel::factory()->make(['email' => 'original@example.com', 'name' => 'Original Name', 'role' => 'general']);
                    return array_merge($baseData($userForTest), ['is_active' => false]);
                },
                'passes' => true,
            ],
            'fails_name_is_empty' => [
                'data' => function() use ($baseData) {
                    $userForTest = UserModel::factory()->make(['email' => 'original@example.com', 'name' => 'Original Name', 'role' => 'general']);
                    return array_merge($baseData($userForTest), ['name' => '']);
                },
                'passes' => false,
                'expectedErrors' => ['name' => ['The name field is required.']]
            ],
            'fails_email_is_empty' => [
                'data' => function() use ($baseData) {
                    $userForTest = UserModel::factory()->make(['email' => 'original@example.com', 'name' => 'Original Name', 'role' => 'general']);
                    return array_merge($baseData($userForTest), ['email' => '']);
                },
                'passes' => false,
                'expectedErrors' => ['email' => ['The email field is required.']]
            ],
            // Note: 'password' is nullable, so sending empty/null is fine.
        ];
    }
} 