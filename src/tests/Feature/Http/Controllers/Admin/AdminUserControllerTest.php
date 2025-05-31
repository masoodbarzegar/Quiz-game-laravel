<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Session;
use PHPUnit\Framework\Attributes\Test;

class AdminUserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create an admin user with manager role
        $this->admin = User::factory()->create([
            'role' => 'manager',
            'is_active' => true
        ]);
    }

    protected function getCsrfToken()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.users.create'));
        
        return csrf_token();
    }

    #[Test]
    public function manager_can_create_new_admin_user(): void
    {
        $token = $this->getCsrfToken();

        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'corrector',
            'is_active' => true,
            '_token' => $token
        ];

        $response = $this->actingAs($this->admin, 'admin')
            ->withSession(['_token' => $token])
            ->post(route('admin.users.store'), $userData);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success', 'User created successfully.');

        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'corrector',
            'is_active' => true
        ]);
    }

    #[Test]
    public function non_manager_cannot_create_admin_user(): void
    {
        $nonManager = User::factory()->create([
            'role' => 'corrector',
            'is_active' => true
        ]);

        $token = $this->getCsrfToken();

        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'corrector',
            'is_active' => true,
            '_token' => $token
        ];

        $response = $this->actingAs($nonManager, 'admin')
            ->withSession(['_token' => $token])
            ->post(route('admin.users.store'), $userData);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    #[Test]
    public function validation_rules_are_enforced_when_creating_user(): void
    {
        $token = $this->getCsrfToken();

        $response = $this->actingAs($this->admin, 'admin')
            ->withSession(['_token' => $token])
            ->post(route('admin.users.store'), [
                'name' => '',
                'email' => 'invalid-email',
                'password' => 'short',
                'password_confirmation' => 'different',
                'role' => 'invalid-role',
                '_token' => $token
            ]);

        $response->assertSessionHasErrors(['name', 'email', 'password', 'role']);
    }
} 