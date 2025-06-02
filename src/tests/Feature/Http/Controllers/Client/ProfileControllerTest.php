<?php

namespace Tests\Feature\Http\Controllers\Client;

use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Inertia\Testing\AssertableInertia as Assert; // Uncommented
use Illuminate\Support\Facades\Hash;

class ProfileControllerTest extends TestCase
{
    use RefreshDatabase;

    protected Client $clientUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->clientUser = Client::factory()->create();
    }

    #[Test] 
    public function client_can_view_their_profile_when_authenticated()
    {
        $this->actingAs($this->clientUser, 'client');

        $response = $this->get(route('profile'));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Client/Profile') // Controller uses Client/Profile
            ->has('auth.user')
            ->has('gameHistory') // Check for game history prop
            ->where('auth.user.id', $this->clientUser->id)
        );
    }

    #[Test]
    public function guest_is_redirected_when_trying_to_view_profile()
    {
        $response = $this->get(route('profile'));

        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function client_can_update_their_profile()
    {
        $this->actingAs($this->clientUser, 'client');

        $updateData = [
            'name' => 'New Name',
            'email' => 'newemail@example.com',
            '_token' => csrf_token(),
        ];

        $response = $this->put(route('profile.update'), $updateData);

        $response->assertRedirect(); 
        $this->clientUser->refresh(); // Refresh the model instance
        $this->assertDatabaseHas('clients', [
            'id' => $this->clientUser->id,
            'name' => 'New Name',
            'email' => 'newemail@example.com',
        ]);
    }

    #[Test]
    public function client_profile_update_requires_valid_data(): void
    {
        $this->actingAs($this->clientUser, 'client');

        $response = $this->put(route('profile.update'), [
            'name' => '', // Invalid: name is required
            'email' => 'not-an-email', // Invalid: email format
            '_token' => csrf_token(),
        ]);

        $response->assertSessionHasErrors(['name', 'email']);

        // Ensure original data is not changed
        $this->clientUser->refresh();
        $this->assertNotEquals('', $this->clientUser->name);
        $this->assertNotEquals('not-an-email', $this->clientUser->email);
    }

    #[Test]
    public function client_profile_update_rejects_duplicate_email_for_another_user(): void
    {
        $this->actingAs($this->clientUser, 'client');
        $otherClient = Client::factory()->create(['email' => 'other@example.com']);

        $response = $this->put(route('profile.update'), [
            'name' => 'Some Name',
            'email' => 'other@example.com', // This email belongs to $otherClient
            '_token' => csrf_token(),
        ]);

        $response->assertSessionHasErrors('email');
        $this->clientUser->refresh();
        $this->assertNotEquals('other@example.com', $this->clientUser->email);
    }

    #[Test]
    public function client_can_update_profile_with_their_own_current_email(): void
    {
        $this->actingAs($this->clientUser, 'client');

        $updateData = [
            'name' => 'Updated Name Again',
            'email' => $this->clientUser->email, // Using the same email
            '_token' => csrf_token(),
        ];

        $response = $this->put(route('profile.update'), $updateData);
        $response->assertRedirect(); // Or specific route
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('clients', [
            'id' => $this->clientUser->id,
            'name' => 'Updated Name Again',
            'email' => $this->clientUser->email,
        ]);
    }

    #[Test]
    public function client_can_update_their_password_with_valid_data(): void
    {
        $this->actingAs($this->clientUser, 'client');

        // Ensure the client has a known password for 'current_password' check
        $currentPassword = 'oldPassword123';
        $this->clientUser->password = Hash::make($currentPassword);
        $this->clientUser->save();

        $newPassword = 'newPassword456';
        $response = $this->put(route('profile.password.update'), [
            'current_password' => $currentPassword,
            'password' => $newPassword,
            'password_confirmation' => $newPassword,
            '_token' => csrf_token(),
        ]);

        $response->assertRedirect(); // Or specific route if different from `back()`
        $response->assertSessionHas('success');
        $this->clientUser->refresh();
        $this->assertTrue(Hash::check($newPassword, $this->clientUser->password));
    }

    #[Test]
    public function client_password_update_fails_with_incorrect_current_password(): void
    {
        $this->actingAs($this->clientUser, 'client');

        $this->clientUser->password = Hash::make('actualOldPassword');
        $this->clientUser->save();

        $newPassword = 'newPassword456';
        $response = $this->put(route('profile.password.update'), [
            'current_password' => 'wrongOldPassword',
            'password' => $newPassword,
            'password_confirmation' => $newPassword,
            '_token' => csrf_token(),
        ]);

        $response->assertSessionHasErrors('current_password');
        $this->clientUser->refresh();
        $this->assertFalse(Hash::check($newPassword, $this->clientUser->password));
    }

    #[Test]
    public function client_password_update_requires_valid_new_password(): void
    {
        $this->actingAs($this->clientUser, 'client');

        $currentPassword = 'oldPassword123';
        $this->clientUser->password = Hash::make($currentPassword);
        $this->clientUser->save();

        // Test short password
        $responseShort = $this->put(route('profile.password.update'), [
            'current_password' => $currentPassword,
            'password' => 'short',
            'password_confirmation' => 'short',
            '_token' => csrf_token(),
        ]);
        $responseShort->assertSessionHasErrors('password');

        // Test password confirmation mismatch
        $responseMismatch = $this->put(route('profile.password.update'), [
            'current_password' => $currentPassword,
            'password' => 'newValidPassword',
            'password_confirmation' => 'differentPassword',
            '_token' => csrf_token(),
        ]);
        $responseMismatch->assertSessionHasErrors('password');

        $this->clientUser->refresh();
        $this->assertTrue(Hash::check($currentPassword, $this->clientUser->password)); // Password should not have changed
    }

    // TODO: Add tests for other actions in ProfileController (e.g., password update if separate)
} 