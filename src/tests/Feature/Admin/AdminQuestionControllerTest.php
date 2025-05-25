<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Question;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Session;

class AdminQuestionControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $manager;
    protected $corrector;
    protected $general;
    protected $question;

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

        // Create a sample question
        $this->question = Question::factory()->create([
            'created_by' => $this->general->id,
            'status' => 'pending'
        ]);
    }

    protected function getCsrfToken()
    {
        $response = $this->actingAs($this->manager, 'admin')
            ->get(route('admin.questions.index'));
        
        return csrf_token();
    }

    protected function withCsrfToken($method, $route, $data = [], $user = null)
    {
        $token = $this->getCsrfToken();
        $user = $user ?? $this->manager;
        
        return $this->actingAs($user, 'admin')
            ->withSession(['_token' => $token])
            ->withHeader('X-CSRF-TOKEN', $token)
            ->$method($route, array_merge($data, ['_token' => $token]));
    }

    public function test_manager_can_view_questions_list(): void
    {
        $response = $this->withCsrfToken('get', route('admin.questions.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($assert) => $assert
            ->component('Admin/Questions/Index')
            ->has('questions')
            ->has('filters')
            ->has('categories')
            ->has('can')
        );
    }

    public function test_corrector_can_view_questions_list(): void
    {
        $response = $this->withCsrfToken('get', route('admin.questions.index'), [], $this->corrector);

        $response->assertStatus(200);
    }

    public function test_general_admin_can_view_questions_list(): void
    {
        $response = $this->withCsrfToken('get', route('admin.questions.index'), [], $this->general);

        $response->assertStatus(200);
    }

    public function test_manager_can_create_question(): void
    {
        $questionData = [
            'question_text' => 'Test Question?',
            'choices' => ['Choice 1', 'Choice 2', 'Choice 3', 'Choice 4'],
            'correct_choice' => 1,
            'explanation' => 'Test explanation',
            'difficulty_level' => 'easy',
            'category' => 'Test Category'
        ];

        $response = $this->withCsrfToken('post', route('admin.questions.store'), $questionData);

        $response->assertRedirect(route('admin.questions.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('questions', [
            'question_text' => 'Test Question?',
            'difficulty_level' => 'easy',
            'category' => 'Test Category',
            'status' => 'pending'
        ]);
    }

    public function test_general_admin_can_create_question(): void
    {
        $questionData = [
            'question_text' => 'Test Question?',
            'choices' => ['Choice 1', 'Choice 2', 'Choice 3', 'Choice 4'],
            'correct_choice' => 1,
            'explanation' => 'Test explanation',
            'difficulty_level' => 'easy',
            'category' => 'Test Category'
        ];

        $response = $this->withCsrfToken('post', route('admin.questions.store'), $questionData, $this->general);

        $response->assertRedirect(route('admin.questions.index'));
        $response->assertSessionHas('success');
    }

    public function test_corrector_cannot_create_question(): void
    {
        $questionData = [
            'question_text' => 'Test Question?',
            'choices' => ['Choice 1', 'Choice 2', 'Choice 3', 'Choice 4'],
            'correct_choice' => 1,
            'explanation' => 'Test explanation',
            'difficulty_level' => 'easy',
            'category' => 'Test Category'
        ];

        $response = $this->withCsrfToken('post', route('admin.questions.store'), $questionData, $this->corrector);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_manager_can_edit_question(): void
    {
        $updateData = [
            'question_text' => 'Updated Question?',
            'choices' => ['Updated 1', 'Updated 2', 'Updated 3', 'Updated 4'],
            'correct_choice' => 2,
            'explanation' => 'Updated explanation',
            'difficulty_level' => 'medium',
            'category' => 'Updated Category'
        ];

        $response = $this->withCsrfToken('put', route('admin.questions.update', $this->question), $updateData);

        $response->assertRedirect(route('admin.questions.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('questions', [
            'id' => $this->question->id,
            'question_text' => 'Updated Question?',
            'difficulty_level' => 'medium',
            'category' => 'Updated Category'
        ]);
    }

    public function test_manager_can_delete_question(): void
    {
        $response = $this->withCsrfToken('delete', route('admin.questions.destroy', $this->question));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertSoftDeleted('questions', [
            'id' => $this->question->id
        ]);
    }

    public function test_corrector_cannot_delete_question(): void
    {
        $response = $this->withCsrfToken('delete', route('admin.questions.destroy', $this->question), [], $this->corrector);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('questions', [
            'id' => $this->question->id
        ]);
    }

    public function test_manager_can_approve_question(): void
    {
        $response = $this->withCsrfToken('post', route('admin.questions.approve', $this->question));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('questions', [
            'id' => $this->question->id,
            'status' => 'approved',
            'approved_by' => $this->manager->id
        ]);
    }

    public function test_corrector_can_approve_question(): void
    {
        $response = $this->withCsrfToken('post', route('admin.questions.approve', $this->question), [], $this->corrector);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('questions', [
            'id' => $this->question->id,
            'status' => 'approved',
            'approved_by' => $this->corrector->id
        ]);
    }

    public function test_manager_can_reject_question(): void
    {
        $rejectData = [
            'rejection_reason' => 'This question needs improvement'
        ];

        $response = $this->withCsrfToken('post', route('admin.questions.reject', $this->question), $rejectData);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('questions', [
            'id' => $this->question->id,
            'status' => 'rejected',
            'rejected_by' => $this->manager->id,
            'rejection_reason' => 'This question needs improvement'
        ]);
    }

    public function test_validation_rules_are_enforced_when_creating_question(): void
    {
        $invalidData = [
            'question_text' => '',
            'choices' => ['Only one choice'],
            'correct_choice' => 5,
            'difficulty_level' => 'invalid'
        ];

        $response = $this->withCsrfToken('post', route('admin.questions.store'), $invalidData);

        $response->assertSessionHasErrors([
            'question_text',
            'choices',
            'correct_choice',
            'difficulty_level'
        ]);
    }

    public function test_filter_by_status(): void
    {
        // Create two approved questions
        Question::factory()->create(['status' => 'approved']);
        Question::factory()->create(['status' => 'approved']);
        $response = $this->withCsrfToken('get', route('admin.questions.index', ['status' => 'approved']));
        $response->assertStatus(200);
        $response->assertInertia(fn ($assert) => $assert
            ->component('Admin/Questions/Index')
            ->has('questions.data', 2)
        );
    }

    public function test_filter_by_difficulty(): void
    {
        // Create two hard questions so that the assertion (expecting 2) passes.
        Question::factory()->create(['difficulty_level' => 'hard']);
        Question::factory()->create(['difficulty_level' => 'hard']);
        $response = $this->withCsrfToken('get', route('admin.questions.index', ['difficulty' => 'hard']));
        $response->assertStatus(200);
        $response->assertInertia(fn ($assert) => $assert
            ->component('Admin/Questions/Index')
            ->has('questions.data', 2)
        );
    }

    public function test_filter_by_category(): void
    {
        // Create one Math question
        Question::factory()->create(['category' => 'Math']);
        $response = $this->withCsrfToken('get', route('admin.questions.index', ['category' => 'Math']));
        $response->assertStatus(200);
        $response->assertInertia(fn ($assert) => $assert
            ->component('Admin/Questions/Index')
            ->has('questions.data', 1)
        );
    }
} 