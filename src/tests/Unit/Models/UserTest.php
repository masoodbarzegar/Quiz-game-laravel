<?php

namespace Tests\Unit\Models;

use App\Models\User;
use App\Models\Question;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;

class UserTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $manager;
    protected $corrector;
    protected $general;

    protected function setUp(): void
    {
        parent::setUp();

        // Create users with different roles
        $this->manager = User::factory()->create([
            'role' => 'manager',
            'is_active' => true
        ]);

        $this->corrector = User::factory()->create([
            'role' => 'corrector',
            'is_active' => true
        ]);

        $this->general = User::factory()->create([
            'role' => 'general',
            'is_active' => true
        ]);

        $this->user = User::factory()->create([
            'role' => 'general',
            'is_active' => true
        ]);
    }

    #[Test]
    public function user_has_correct_fillable_attributes(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'general',
            'is_active' => true
        ]);

        $this->assertEquals('Test User', $user->name);
        $this->assertEquals('test@example.com', $user->email);
        $this->assertEquals('general', $user->role);
        $this->assertTrue($user->is_active);
    }

    #[Test]
    public function user_password_is_hashed(): void
    {
        $password = 'password123';
        $user = User::factory()->create(['password' => $password]);

        $this->assertNotEquals($password, $user->password);
        $this->assertTrue(Hash::check($password, $user->password));
    }

    #[Test]
    public function user_has_hidden_attributes(): void
    {
        $user = User::factory()->create();
        $userArray = $user->toArray();

        $this->assertArrayNotHasKey('password', $userArray);
        $this->assertArrayNotHasKey('remember_token', $userArray);
    }

    #[Test]
    public function user_has_correct_casts(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'is_active' => true
        ]);

        $this->assertInstanceOf(\DateTime::class, $user->email_verified_at);
        $this->assertIsBool($user->is_active);
    }

    #[Test]
    public function user_has_role_methods(): void
    {
        // Test hasRole method
        $this->assertTrue($this->manager->hasRole('manager'));
        $this->assertTrue($this->manager->hasRole(['manager', 'corrector']));
        $this->assertFalse($this->manager->hasRole('corrector'));

        // Test hasAnyRole method
        $this->assertTrue($this->manager->hasAnyRole(['manager', 'corrector']));
        $this->assertFalse($this->manager->hasAnyRole(['corrector', 'general']));

        // Test specific role methods
        $this->assertTrue($this->manager->isManager());
        $this->assertFalse($this->manager->isCorrector());
        $this->assertFalse($this->manager->isGeneral());

        $this->assertTrue($this->corrector->isCorrector());
        $this->assertFalse($this->corrector->isManager());
        $this->assertFalse($this->corrector->isGeneral());

        $this->assertTrue($this->general->isGeneral());
        $this->assertFalse($this->general->isManager());
        $this->assertFalse($this->general->isCorrector());
    }

    #[Test]
    public function user_relationships_with_questions(): void
    {
        // Create questions for the user
        $createdQuestion = Question::factory()->create([
            'created_by' => $this->user->id,
            'status' => 'pending'
        ]);

        $approvedQuestion = Question::factory()->create([
            'approved_by' => $this->user->id,
            'status' => 'approved'
        ]);

        $rejectedQuestion = Question::factory()->create([
            'rejected_by' => $this->user->id,
            'status' => 'rejected'
        ]);

        // Refresh user to reload relationships
        $user = User::with(['createdQuestions', 'approvedQuestions', 'rejectedQuestions'])->find($this->user->id);

        // Test created questions relationship
        $this->assertCount(1, $this->user->createdQuestions);
        $this->assertTrue($this->user->createdQuestions->contains($createdQuestion));

        // Test approved questions relationship
        $this->assertCount(1, $this->user->approvedQuestions);
        $this->assertTrue($this->user->approvedQuestions->contains($approvedQuestion));

        // Test rejected questions relationship
        $this->assertCount(1, $this->user->rejectedQuestions);
        $this->assertTrue($this->user->rejectedQuestions->contains($rejectedQuestion));
    }

    #[Test]
    public function user_can_be_activated_and_deactivated(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        
        // Test deactivation
        $user->update(['is_active' => false]);
        $this->assertFalse($user->fresh()->is_active);

        // Test activation
        $user->update(['is_active' => true]);
        $this->assertTrue($user->fresh()->is_active);
    }

    #[Test]
    public function user_email_verification(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null
        ]);

        $this->assertNull($user->email_verified_at);
        $this->assertFalse($user->hasVerifiedEmail());

        $user->markEmailAsVerified();
        
        $this->assertNotNull($user->email_verified_at);
        $this->assertTrue($user->hasVerifiedEmail());
    }

    #[Test]
    public function user_remember_token(): void
    {
        $user = User::factory()->create();
        $token = 'remember_token_123';

        $user->setRememberToken($token);
        $this->assertEquals($token, $user->getRememberToken());

        $user->setRememberToken(null);
        $rememberToken = $user->getRememberToken();
        $this->assertTrue($rememberToken === null || $rememberToken === '');
    }

    #[Test]
    public function user_notification_preferences(): void
    {
        $user = User::factory()->create();
        
        // Test notification routing
        $this->assertEquals($user->email, $user->routeNotificationForMail());
    }

    #[Test]
    public function user_factory_creates_valid_user(): void
    {
        $user = User::factory()->create();

        $this->assertNotNull($user->name);
        $this->assertNotNull($user->email);
        $this->assertNotNull($user->password);
        $this->assertNotNull($user->role);
        $this->assertIsBool($user->is_active);
    }

    #[Test]
    public function user_can_have_multiple_roles_checked(): void
    {
        $this->assertTrue($this->manager->hasRole(['manager', 'corrector']));
        $this->assertFalse($this->manager->hasRole(['corrector', 'general']));
        
        $this->assertTrue($this->corrector->hasRole(['manager', 'corrector']));
        $this->assertFalse($this->corrector->hasRole(['manager', 'general']));
        
        $this->assertTrue($this->general->hasRole(['manager', 'general']));
        $this->assertFalse($this->general->hasRole(['manager', 'corrector']));
    }

    #[Test]
    public function user_has_many_created_approved_rejected_questions(): void
    {
        $user = User::factory()->create();
        Question::factory()->count(2)->create(['created_by' => $user->id]);
        Question::factory()->count(3)->create(['approved_by' => $user->id]);
        Question::factory()->count(4)->create(['rejected_by' => $user->id]);

        $this->assertCount(2, $user->createdQuestions);
        $this->assertCount(3, $user->approvedQuestions);
        $this->assertCount(4, $user->rejectedQuestions);
    }

    #[Test]
    public function has_role_checks_correctly_for_single_role(): void
    {
        $user = User::factory()->make(['role' => 'manager']);
        $this->assertTrue($user->hasRole('manager'));
        $this->assertFalse($user->hasRole('corrector'));
    }

    #[Test]
    public function has_role_checks_correctly_for_array_of_roles(): void
    {
        $user = User::factory()->make(['role' => 'corrector']);
        $this->assertTrue($user->hasRole(['manager', 'corrector']));
        $this->assertFalse($user->hasRole(['manager', 'general']));
    }

    #[Test]
    public function has_any_role_checks_correctly(): void
    {
        $manager = User::factory()->make(['role' => 'manager']);
        $this->assertTrue($manager->hasAnyRole(['manager', 'admin']));
        $this->assertFalse($manager->hasAnyRole(['corrector', 'general']));

        $general = User::factory()->make(['role' => 'general']);
        $this->assertTrue($general->hasAnyRole(['general', 'user']));
    }

    #[Test]
    public function role_specific_methods_work_correctly(): void
    {
        $manager = User::factory()->make(['role' => 'manager']);
        $this->assertTrue($manager->isManager());
        $this->assertFalse($manager->isCorrector());
        $this->assertFalse($manager->isGeneral());

        $corrector = User::factory()->make(['role' => 'corrector']);
        $this->assertFalse($corrector->isManager());
        $this->assertTrue($corrector->isCorrector());
        $this->assertFalse($corrector->isGeneral());

        $general = User::factory()->make(['role' => 'general']);
        $this->assertFalse($general->isManager());
        $this->assertFalse($general->isCorrector());
        $this->assertTrue($general->isGeneral());
    }

    #[Test]
    public function route_notification_for_mail_returns_email(): void
    {
        $email = 'test@example.com';
        $user = User::factory()->make(['email' => $email]);
        $this->assertEquals($email, $user->routeNotificationForMail());
    }
} 