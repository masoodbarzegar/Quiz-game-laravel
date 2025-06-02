<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Question;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Session;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Log;

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

    #[Test]
    public function manager_can_view_questions_list(): void
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

    #[Test]
    public function corrector_can_view_questions_list(): void
    {
        $response = $this->withCsrfToken('get', route('admin.questions.index'), [], $this->corrector);

        $response->assertStatus(200);
    }

    #[Test]
    public function general_admin_can_view_questions_list(): void
    {
        $response = $this->withCsrfToken('get', route('admin.questions.index'), [], $this->general);

        $response->assertStatus(200);
    }

    #[Test]
    public function general_admin_sees_only_own_questions_in_list(): void
    {
        // Create another question by a different user (manager for simplicity)
        $otherQuestion = Question::factory()->create([
            'created_by' => $this->manager->id,
            'status' => 'pending'
        ]);

        // $this->question is created by $this->general in setUp()

        $response = $this->withCsrfToken('get', route('admin.questions.index'), [], $this->general);

        $response->assertStatus(200);
        $response->assertInertia(fn ($assert) => $assert
            ->component('Admin/Questions/Index')
            ->has('questions.data', 1) // Should only see 1 question
            ->where('questions.data.0.id', $this->question->id) // Ensure it's their own question
        );
    }

    #[Test]
    public function general_user_can_access_edit_page_for_own_pending_question(): void
    {
        // $this->question is created by $this->general and is 'pending' in setUp()
        $response = $this->withCsrfToken('get', route('admin.questions.edit', $this->question), [], $this->general);

        $response->assertStatus(200);
        $response->assertInertia(fn ($assert) => $assert
            ->component('Admin/Questions/Edit')
            ->has('question')
            ->where('question.id', $this->question->id)
        );
    }

    #[Test]
    public function general_user_cannot_access_edit_page_for_own_approved_question(): void
    {
        $this->question->update(['status' => 'approved', 'approved_by' => $this->manager->id]);

        $response = $this->withCsrfToken('get', route('admin.questions.edit', $this->question), [], $this->general);

        $response->assertStatus(403);
    }

    #[Test]
    public function general_user_cannot_access_edit_page_for_others_question(): void
    {
        $otherUserQuestion = Question::factory()->create(['created_by' => $this->manager->id]);

        $response = $this->withCsrfToken('get', route('admin.questions.edit', $otherUserQuestion), [], $this->general);

        $response->assertStatus(403);
    }

    #[Test]
    public function corrector_can_access_edit_page_for_any_question(): void
    {
        // Question created by general user
        $responseGeneral = $this->withCsrfToken('get', route('admin.questions.edit', $this->question), [], $this->corrector);
        $responseGeneral->assertStatus(200);
        $responseGeneral->assertInertia(fn ($assert) => $assert->component('Admin/Questions/Edit'));

        // Question created by another user (manager)
        $managerQuestion = Question::factory()->create(['created_by' => $this->manager->id]);
        $responseManager = $this->withCsrfToken('get', route('admin.questions.edit', $managerQuestion), [], $this->corrector);
        $responseManager->assertStatus(200);
        $responseManager->assertInertia(fn ($assert) => $assert->component('Admin/Questions/Edit'));
    }

    #[Test]
    public function manager_can_create_question(): void
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

    #[Test]
    public function general_admin_can_create_question(): void
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

    #[Test]
    public function corrector_cannot_create_question(): void
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

    #[Test]
    public function manager_can_edit_question_and_approve_it_via_update(): void
    {
        $this->assertNull($this->question->approved_by);
        $this->assertNull($this->question->approved_at);

        $updateData = [
            'question_text' => 'Updated Question by Manager and Approved',
            'choices' => $this->question->choices,
            'correct_choice' => $this->question->correct_choice,
            'explanation' => $this->question->explanation,
            'difficulty_level' => $this->question->difficulty_level,
            'category' => $this->question->category,
            'status' => 'approved',
        ];

        $response = $this->withCsrfToken('put', route('admin.questions.update', $this->question), $updateData, $this->manager);

        $response->assertRedirect(route('admin.questions.index'));
        $response->assertSessionHas('success');

        $this->question->refresh();
        $this->assertEquals('Updated Question by Manager and Approved', $this->question->question_text);
        $this->assertEquals('approved', $this->question->status);
        $this->assertEquals($this->manager->id, $this->question->approved_by);
        $this->assertNotNull($this->question->approved_at);
        $this->assertNull($this->question->rejected_by);
        $this->assertNull($this->question->rejected_at);
        $this->assertNull($this->question->rejection_reason);
    }

    #[Test]
    public function manager_can_edit_question_and_reject_it_via_update(): void
    {
        $updateData = [
            'question_text' => 'Updated Question by Manager and Rejected',
            'choices' => $this->question->choices,
            'correct_choice' => $this->question->correct_choice,
            'explanation' => $this->question->explanation,
            'difficulty_level' => $this->question->difficulty_level,
            'category' => $this->question->category,
            'status' => 'rejected',
            'rejection_reason' => 'Rejected during update by manager'
        ];

        $response = $this->withCsrfToken('put', route('admin.questions.update', $this->question), $updateData, $this->manager);
        $response->assertRedirect(route('admin.questions.index'));

        $this->question->refresh();
        $this->assertEquals('rejected', $this->question->status);
        $this->assertEquals($this->manager->id, $this->question->rejected_by);
        $this->assertNotNull($this->question->rejected_at);
        $this->assertEquals('Rejected during update by manager', $this->question->rejection_reason);
        $this->assertNull($this->question->approved_by);
        $this->assertNull($this->question->approved_at);
    }
    
    #[Test]
    public function corrector_can_update_question_and_approve_it_via_update(): void
    {
        $questionToApprove = Question::factory()->create(['status' => 'pending', 'created_by' => $this->general->id]);
        $updateData = [
            'question_text' => 'Updated and Approved by Corrector',
            'choices' => $questionToApprove->choices,
            'correct_choice' => $questionToApprove->correct_choice,
            'explanation' => $questionToApprove->explanation,
            'difficulty_level' => $questionToApprove->difficulty_level,
            'category' => $questionToApprove->category,
            'status' => 'approved',
        ];

        $response = $this->withCsrfToken('put', route('admin.questions.update', $questionToApprove), $updateData, $this->corrector);
        $response->assertRedirect(route('admin.questions.index'));

        $questionToApprove->refresh();
        $this->assertEquals('approved', $questionToApprove->status);
        $this->assertEquals($this->corrector->id, $questionToApprove->approved_by);
        $this->assertNotNull($questionToApprove->approved_at);
    }

    #[Test]
    public function manager_can_edit_question(): void
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

    #[Test]
    public function general_user_can_update_own_pending_question(): void
    {
        // $this->question is created by $this->general and is 'pending' in setUp()
        $updateData = [
            'question_text' => 'Updated by General User',
            'choices' => $this->question->choices, // Ensure all fields are present
            'correct_choice' => $this->question->correct_choice,
            'explanation' => $this->question->explanation,
            'difficulty_level' => $this->question->difficulty_level,
            'category' => $this->question->category,
            'status' => $this->question->status, // Keep original status unless changing it
        ];
        $response = $this->withCsrfToken('put', route('admin.questions.update', $this->question), $updateData, $this->general);

        $response->assertRedirect(route('admin.questions.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('questions', ['id' => $this->question->id, 'question_text' => 'Updated by General User']);
    }

    #[Test]
    public function general_user_can_update_own_rejected_question(): void
    {
        $this->question->update(['status' => 'rejected', 'rejected_by' => $this->manager->id, 'rejection_reason' => 'initial test reason']);
        $updateData = [
            'question_text' => 'Updated Rejected by General',
            'choices' => $this->question->choices,
            'correct_choice' => $this->question->correct_choice,
            'explanation' => $this->question->explanation,
            'difficulty_level' => $this->question->difficulty_level,
            'category' => $this->question->category,
            'status' => $this->question->status, // Status is 'rejected'
            'rejection_reason' => $this->question->rejection_reason, // Carry over the existing reason
        ];
        $response = $this->withCsrfToken('put', route('admin.questions.update', $this->question), $updateData, $this->general);

        $response->assertRedirect(route('admin.questions.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('questions', ['id' => $this->question->id, 'question_text' => 'Updated Rejected by General']);
    }

    #[Test]
    public function general_user_cannot_update_own_approved_question(): void
    {
        $this->question->update(['status' => 'approved', 'approved_by' => $this->manager->id, 'approved_at' => now()]); // Ensure approved_at is set for consistency
        $updateData = [
            'question_text' => 'Attempt Update Approved',
            'choices' => $this->question->choices,
            'correct_choice' => $this->question->correct_choice,
            'explanation' => $this->question->explanation,
            'difficulty_level' => $this->question->difficulty_level,
            'category' => $this->question->category,
            'status' => $this->question->status,
        ];
        $response = $this->withCsrfToken('put', route('admin.questions.update', $this->question), $updateData, $this->general);
        Log::info('Response content: Policy: ');
        Log::info($response->getContent());
        $response->assertStatus(403);
        $this->assertDatabaseMissing('questions', ['id' => $this->question->id, 'question_text' => 'Attempt Update Approved']);
    }

    #[Test]
    public function general_user_cannot_update_others_question(): void
    {
        $otherUserQuestion = Question::factory()->create(['created_by' => $this->manager->id, 'status' => 'pending']);
        $updateData = [
            'question_text' => 'Attempt Update Others',
            'choices' => $otherUserQuestion->choices,
            'correct_choice' => $otherUserQuestion->correct_choice,
            'explanation' => $otherUserQuestion->explanation,
            'difficulty_level' => $otherUserQuestion->difficulty_level,
            'category' => $otherUserQuestion->category,
            'status' => $otherUserQuestion->status,
        ];
        $response = $this->withCsrfToken('put', route('admin.questions.update', $otherUserQuestion), $updateData, $this->general);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('questions', ['id' => $otherUserQuestion->id, 'question_text' => 'Attempt Update Others']);
    }

    #[Test]
    public function corrector_can_update_any_question(): void
    {
        // Question created by general user
        $updateDataGeneral = [
            'question_text' => 'Corrected General User Question',
            'choices' => $this->question->choices,
            'correct_choice' => $this->question->correct_choice,
            'explanation' => $this->question->explanation,
            'difficulty_level' => $this->question->difficulty_level,
            'category' => $this->question->category,
            'status' => $this->question->status,
        ];
        $responseGeneral = $this->withCsrfToken('put', route('admin.questions.update', $this->question), $updateDataGeneral, $this->corrector);
        $responseGeneral->assertRedirect(route('admin.questions.index'));
        $this->assertDatabaseHas('questions', ['id' => $this->question->id, 'question_text' => 'Corrected General User Question']);

        // Question created by another user (manager)
        $managerQuestion = Question::factory()->create(['created_by' => $this->manager->id, 'status' => 'pending']);
        $updateDataManager = [
            'question_text' => 'Corrected Manager Question',
            'choices' => $managerQuestion->choices,
            'correct_choice' => $managerQuestion->correct_choice,
            'explanation' => $managerQuestion->explanation,
            'difficulty_level' => $managerQuestion->difficulty_level,
            'category' => $managerQuestion->category,
            'status' => $managerQuestion->status,
        ];
        $responseManager = $this->withCsrfToken('put', route('admin.questions.update', $managerQuestion), $updateDataManager, $this->corrector);
        $responseManager->assertRedirect(route('admin.questions.index'));
        $this->assertDatabaseHas('questions', ['id' => $managerQuestion->id, 'question_text' => 'Corrected Manager Question']);
    }

    #[Test]
    public function manager_can_delete_question(): void
    {
        $response = $this->withCsrfToken('delete', route('admin.questions.destroy', $this->question));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertSoftDeleted('questions', [
            'id' => $this->question->id
        ]);
    }

    #[Test]
    public function corrector_cannot_delete_question(): void
    {
        $response = $this->withCsrfToken('delete', route('admin.questions.destroy', $this->question), [], $this->corrector);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('questions', [
            'id' => $this->question->id
        ]);
    }

    #[Test]
    public function general_cannot_delete_question(): void
    {
        $response = $this->withCsrfToken('delete', route('admin.questions.destroy', $this->question), [], $this->general);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->question->refresh();

        $this->assertDatabaseHas('questions', ['id' => $this->question->id]); // Ensure not deleted
    }

    #[Test]
    public function manager_can_approve_question(): void
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

    #[Test]
    public function general_cannot_approve_question(): void
    {
        $response = $this->withCsrfToken('post', route('admin.questions.approve', $this->question), [], $this->general);
        
        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->question->refresh();

        $this->assertNotEquals('approved', $this->question->status);
    }

    #[Test]
    public function corrector_can_approve_question(): void
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

    #[Test]
    public function manager_can_reject_question(): void
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

    #[Test]
    public function general_cannot_reject_question(): void
    {
        $rejectData = ['rejection_reason' => 'Attempt to reject by general'];
       
        $response = $this->withCsrfToken('post', route('admin.questions.reject', $this->question), $rejectData, $this->general);
        
        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->question->refresh();
        $this->assertNotEquals('rejected', $this->question->status);
    }

    #[Test]
    public function validation_rules_are_enforced_when_creating_question(): void
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

    #[Test]
    public function filter_by_status(): void
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

    #[Test]
    public function filter_by_difficulty(): void
    {
        // Ensure a clean slate for this specific filter test
        Question::query()->delete(); 

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

    #[Test]
    public function filter_by_category(): void
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