<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Question;
use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Session;
use PHPUnit\Framework\Attributes\Test;

class AdminDashboardControllerTest extends TestCase
{
    use RefreshDatabase;

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

        // Create test data
        Client::factory()->count(3)->create(['is_active' => true]);
        Client::factory()->count(2)->create(['is_active' => false]);
        
        Question::factory()->approved()->count(2)->create();
        Question::factory()->pending()->count(3)->create();
        Question::factory()->rejected()->count(1)->create();
    }

    protected function getCsrfToken()
    {
        $response = $this->actingAs($this->manager, 'admin')
            ->get(route('admin.dashboard'));
        
        return csrf_token();
    }
    
    #[Test]
    public function manager_can_access_dashboard(): void
    {
        $response = $this->actingAs($this->manager, 'admin')
            ->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($assert) => $assert
            ->component('Admin/Dashboard')
            ->has('stats')
            ->has('roleData')
            ->has('flash')
        );
    }

    #[Test]
    public function corrector_can_access_dashboard(): void
    {
        $response = $this->actingAs($this->corrector, 'admin')
            ->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($assert) => $assert
            ->component('Admin/Dashboard')
            ->has('stats')
            ->has('roleData')
            ->has('flash')
        );
    }

    #[Test]
    public function general_admin_can_access_dashboard(): void
    {
        $response = $this->actingAs($this->general, 'admin')
            ->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($assert) => $assert
            ->component('Admin/Dashboard')
            ->has('stats')
            ->has('roleData')
            ->has('flash')
        );
    }

    #[Test]
    public function dashboard_shows_correct_stats(): void
    {
        $response = $this->actingAs($this->manager, 'admin')
            ->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($assert) => $assert
            ->component('Admin/Dashboard')
            ->where('stats.total_clients', 5)
            ->where('stats.active_clients', 3)
            ->where('stats.total_questions', 6)
            ->where('stats.pending_questions', 3)
            ->where('stats.approved_questions', 2)
            ->where('stats.rejected_questions', 1)
        );
    }

    #[Test]
    public function manager_sees_recent_clients(): void
    {
        $response = $this->actingAs($this->manager, 'admin')
            ->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($assert) => $assert
            ->component('Admin/Dashboard')
            ->has('roleData.recent_clients', 5)
            ->where('roleData.recent_clients.0.is_active', true)
        );
    }

    #[Test]
    public function corrector_sees_pending_questions(): void
    {
        $response = $this->actingAs($this->corrector, 'admin')
            ->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($assert) => $assert
            ->component('Admin/Dashboard')
            ->has('roleData.pending_questions', 3)
            ->where('roleData.pending_questions.0.status', 'pending')
        );
    }

    #[Test]
    public function general_admin_sees_total_questions(): void
    {
        $response = $this->actingAs($this->general, 'admin')
            ->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($assert) => $assert
            ->component('Admin/Dashboard')
            ->where('roleData.total_questions', 6)
        );
    }

    #[Test]
    public function unauthenticated_user_cannot_access_dashboard(): void
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect(route('admin.login'));
    }

    #[Test]
    public function inactive_user_cannot_access_dashboard(): void
    {
        $inactiveUser = User::factory()->inactive()->create([
            'is_active' => false,
            'role' => 'manager'
        ]);

        $response = $this->actingAs($inactiveUser, 'admin')
            ->get(route('admin.dashboard'));

        $response->assertRedirect(route('admin.login'));
        $response->assertSessionHas('error');
    }

    #[Test]
    public function dashboard_updates_stats_in_real_time(): void
    {
        // Initial dashboard view
        $response = $this->actingAs($this->manager, 'admin')
            ->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($assert) => $assert
            ->where('stats.total_questions', 6)
            ->where('stats.pending_questions', 3)
        );

        // Create a new pending question
        Question::factory()->pending()->create();

        // Check updated dashboard
        $response = $this->actingAs($this->manager, 'admin')
            ->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($assert) => $assert
            ->where('stats.total_questions', 7)
            ->where('stats.pending_questions', 4)
        );
    }

    #[Test]
    public function dashboard_shows_flash_messages(): void
    {
        $response = $this->actingAs($this->manager, 'admin')
            ->withSession([
                'error' => 'Test error message',
                'success' => 'Test success message'
            ])
            ->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($assert) => $assert
            ->component('Admin/Dashboard')
            ->where('flash.error', 'Test error message')
            ->where('flash.success', 'Test success message')
        );
    }
} 