<?php

namespace Tests\Unit\Models;

use App\Models\User;
use App\Models\Question;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class QuestionTest extends TestCase
{
    use RefreshDatabase;

    protected $question;
    protected $creator;
    protected $approver;
    protected $rejecter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->creator = User::factory()->create(['role' => 'general']);
        $this->approver = User::factory()->create(['role' => 'corrector']);
        $this->rejecter = User::factory()->create(['role' => 'corrector']);

        // Create a question with all relationships
        $this->question = Question::factory()->create([
            'created_by' => $this->creator->id,
            'approved_by' => $this->approver->id,
            'rejected_by' => $this->rejecter->id,
            'status' => 'rejected',
            'rejection_reason' => 'Test rejection reason',
            'approved_at' => now(),
            'rejected_at' => now()
        ]);
    }

    #[Test]
    public function question_has_creator_relationship(): void
    {
        $this->assertInstanceOf(User::class, $this->question->creator);
        $this->assertEquals($this->creator->id, $this->question->creator->id);
    }

    #[Test]
    public function question_has_approver_relationship(): void
    {
        $this->assertInstanceOf(User::class, $this->question->approver);
        $this->assertEquals($this->approver->id, $this->question->approver->id);
    }

    #[Test]
    public function question_has_rejecter_relationship(): void
    {
        $rejecter = User::factory()->create();
        $question = Question::factory()->create([
            'rejected_by' => $rejecter->id,
        ]);
        
        // Reload question if the relationship is not auto-loaded
        $question->refresh();

        $this->assertInstanceOf(User::class, $question->rejecter);
        $this->assertEquals($rejecter->id, $question->rejecter->id);
    }

    #[Test]
    public function question_scope_pending(): void
    {
        Question::factory()->pending()->count(3)->create();
        
        $pendingQuestions = Question::pending()->get();
        
        $this->assertEquals(3, $pendingQuestions->count());
        $pendingQuestions->each(function ($question) {
            $this->assertEquals('pending', $question->status);
            $this->assertNull($question->approved_by);
            $this->assertNull($question->rejected_by);
        });
    }

    #[Test]
    public function question_scope_approved(): void
    {
        Question::factory()->approved()->count(2)->create();
        
        $approvedQuestions = Question::approved()->get();
        
        $this->assertEquals(2, $approvedQuestions->count());
        $approvedQuestions->each(function ($question) {
            $this->assertEquals('approved', $question->status);
            $this->assertNotNull($question->approved_by);
            $this->assertNotNull($question->approved_at);
        });
    }

    #[Test]
    public function question_scope_rejected(): void
    {
        $rejectedQuestions = Question::rejected()->get();
        
        $this->assertEquals(1, $rejectedQuestions->count());
        $rejectedQuestions->each(function ($question) {
            $this->assertEquals('rejected', $question->status);
            $this->assertNotNull($question->rejected_by);
            $this->assertNotNull($question->rejected_at);
            $this->assertNotNull($question->rejection_reason);
        });
    }

    #[Test]
    public function question_scope_by_difficulty(): void
    {
        // Ensure a clean state before running this test
        Question::query()->delete(); // Or use truncate if you're not using transactions

        Question::factory()->difficulty('easy')->create();
        Question::factory()->difficulty('medium')->create();
        Question::factory()->difficulty('hard')->create();
        
        $easyQuestions = Question::byDifficulty('easy')->get();
        $this->assertEquals(1, $easyQuestions->count());
        $this->assertEquals('easy', $easyQuestions->first()->difficulty_level);
        
        $mediumQuestions = Question::byDifficulty('medium')->get();
        $this->assertEquals(1, $mediumQuestions->count());
        $this->assertEquals('medium', $mediumQuestions->first()->difficulty_level);
        
        $hardQuestions = Question::byDifficulty('hard')->get();
        $this->assertEquals(1, $hardQuestions->count());
        $this->assertEquals('hard', $hardQuestions->first()->difficulty_level);
    }

    #[Test]
    public function question_scope_by_category(): void
    {
        Question::factory()->create(['category' => 'Math']);
        Question::factory()->create(['category' => 'Science']);
        
        $mathQuestions = Question::byCategory('Math')->get();
        $this->assertEquals(1, $mathQuestions->count());
        $this->assertEquals('Math', $mathQuestions->first()->category);
        
        $scienceQuestions = Question::byCategory('Science')->get();
        $this->assertEquals(1, $scienceQuestions->count());
        $this->assertEquals('Science', $scienceQuestions->first()->category);
    }

    #[Test]
    public function question_correct_answer_accessor(): void
    {
        $question = Question::factory()->create([
            'choices' => ['A', 'B', 'C', 'D'],
            'correct_choice' => 2
        ]);
        
        $this->assertEquals('B', $question->correct_answer);
    }

    #[Test]
    public function question_choices_mutator_validates_number_of_choices(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('A question must have exactly 4 choices.');
        
        Question::factory()->create([
            'choices' => ['A', 'B', 'C'] // Only 3 choices
        ]);
    }

    #[Test]
    public function question_correct_choice_mutator_validates_range(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Correct choice must be between 1 and 4.');
        
        Question::factory()->create([
            'choices' => ['A', 'B', 'C', 'D'],
            'correct_choice' => 5 // Invalid choice number
        ]);
    }

    #[Test]
    public function question_choices_mutator_handles_json_string(): void
    {
        $question = Question::factory()->create([
            'choices' => json_encode(['A', 'B', 'C', 'D']),
            'correct_choice' => 1
        ]);
        
        $this->assertIsArray($question->choices);
        $this->assertCount(4, $question->choices);
        $this->assertEquals(['A', 'B', 'C', 'D'], $question->choices);
    }

    #[Test]
    public function question_soft_deletes(): void
    {
        $question = Question::factory()->create();
        $questionId = $question->id;
        
        $question->delete();
        
        $this->assertSoftDeleted('questions', ['id' => $questionId]);
        $this->assertDatabaseHas('questions', ['id' => $questionId]);
    }

    #[Test]
    public function question_casts_attributes(): void
    {
        $question = Question::factory()->create([
            'choices' => ['A', 'B', 'C', 'D'],
            'correct_choice' => 1,
            'created_at' => now(),
            'updated_at' => now(),
            'approved_at' => now(),
            'rejected_at' => now()
        ]);
        
        $this->assertIsArray($question->choices);
        $this->assertIsInt($question->correct_choice);
        $this->assertInstanceOf(\DateTime::class, $question->created_at);
        $this->assertInstanceOf(\DateTime::class, $question->updated_at);
        $this->assertInstanceOf(\DateTime::class, $question->approved_at);
        $this->assertInstanceOf(\DateTime::class, $question->rejected_at);
    }
} 